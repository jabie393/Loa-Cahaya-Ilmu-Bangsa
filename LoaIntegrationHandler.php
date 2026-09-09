<?php

namespace APP\plugins\generic\loaIntegration;

use PKP\handler\PKPHandler;
use APP\facades\Repo;
use PKP\db\DAORegistry;
use Illuminate\Support\Facades\DB;
use PKP\security\Role;
use PKP\core\Core;
use PKP\security\Validation;

class LoaIntegrationHandler extends PKPHandler
{

    private $plugin;

    /**
     * Constructor
     */
    public function __construct($plugin = null)
    {
        parent::__construct();
        $this->plugin = $plugin ?? \PKP\plugins\PluginRegistry::getPlugin('generic', 'loaintegrationplugin');
    }

    /**
     * POST /loa-api/test
     */
    public function test($args, $request)
    {
        $payload = $this->getJsonPayload();
        $this->logRequest('test', $payload);

        if (!$request->isPost()) {
            $this->sendJson(false, 'Method Not Allowed', 405);
        }

        $journalPath = $payload['journal_path'] ?? null;
        if (empty($journalPath)) {
            $this->sendJson(false, 'Missing journal_path parameter', 400);
        }

        // Cari Jurnal berdasarkan path
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journal = $journalDao->getByPath($journalPath);
        if (!$journal) {
            $this->sendJson(false, 'Journal not found: ' . $journalPath, 404);
        }

        // Validasi secret key untuk jurnal tersebut
        $secret = $payload['secret'] ?? null;
        $configuredSecret = $this->plugin->getSetting($journal->getId(), 'secretKey') ?: 'loacibkeyryudevs';
        if ($secret !== $configuredSecret) {
            $this->sendJson(false, 'Unauthorized: Invalid secret key', 401);
        }

        $this->sendJson(true, 'LOA Integration Active', 200, [
            'ojs_version' => '3.4.0.8',
            'api_version' => 'v1',
            'timestamp' => date('Y-m-d H:i:s'),
            'plugin' => 'loaIntegration',
            'journal' => $journalPath,
        ]);
    }

    /**
     * GET /loa-api/health
     */
    public function health($args, $request)
    {
        $this->logRequest('health', []);

        if (!$request->isGet()) {
            $this->sendJson(false, 'Method Not Allowed', 405);
        }

        $this->sendJson(true, 'LOA Integration Plugin Healthy', 200, [
            'plugin' => 'loaIntegration',
            'version' => '1.0.0',
            'ojs_version' => '3.4.0.8',
            'api_version' => 'v1',
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * POST /loa-api/submissions
     */
    public function submissions($args, $request)
    {
        $tmpFilePath = null;
        try {
            $payload = $this->getJsonPayload();
            $this->logRequest('submissions', $payload);

            // 1. Resolve Journal first
            $journalPath = $payload['journal_path'] ?? null;
            if (empty($journalPath)) {
                $this->sendJson(false, 'Missing journal_path parameter', 400);
            }

            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journal = $journalDao->getByPath($journalPath);
            if (!$journal) {
                error_log('LOA SUBMISSION ERROR - Journal not found: ' . $journalPath);
                $this->sendJson(false, 'Journal not found: ' . $journalPath, 404);
            }
            error_log('LOA SUBMISSION - Journal found: ' . $journal->getLocalizedName() . ' (ID: ' . $journal->getId() . ')');

            // Check if submission already exists (for Resubmit or dynamic DOI update)
            $ojsSubId = $payload['ojs_submission_id'] ?? null;
            $existingSubmission = null;
            if (!empty($ojsSubId)) {
                $existingSubmission = Repo::submission()->get((int)$ojsSubId);
            }

            if ($existingSubmission) {
                // Validasi Secret Key berdasarkan setting jurnal target
                $secret = $payload['secret'] ?? null;
                $configuredSecret = $this->plugin->getSetting($journal->getId(), 'secretKey') ?: 'loacibkeyryudevs';
                if ($secret !== $configuredSecret) {
                    error_log('LOA SUBMISSION ERROR - Invalid secret key for journal: ' . $journalPath);
                    $this->sendJson(false, 'Unauthorized: Invalid secret key', 401);
                }

                $publication = $existingSubmission->getCurrentPublication();
                $publicationId = $publication->getId();
                $locale = $journal->getPrimaryLocale() ?: 'en';
                
                // 1. Update DOI in DB
                $customDoi = $payload['doi'] ?? null;
                if (!empty($customDoi)) {
                    $pubRow = DB::table('publications')->where('publication_id', $publicationId)->first();
                    if ($pubRow && !empty($pubRow->doi_id)) {
                        DB::table('dois')->where('doi_id', $pubRow->doi_id)->update([
                            'doi' => $customDoi,
                            'status' => 2,
                        ]);
                    } else {
                        $doiId = DB::table('dois')->insertGetId([
                            'context_id' => $existingSubmission->getData('contextId'),
                            'doi' => $customDoi,
                            'status' => 2,
                        ]);
                        DB::table('publications')->where('publication_id', $publicationId)->update([
                            'doi_id' => $doiId,
                        ]);
                    }
                } else {
                    DB::table('publications')->where('publication_id', $publicationId)->update([
                        'doi_id' => null,
                    ]);
                    error_log('LOA SUBMISSION - Cleared/removed DOI from publication because payload requested No DOI');
                }

                // 2. Update PDF Galley if pdf_url is provided
                $pdfUrl = $payload['pdf_url'] ?? null;
                if (!empty($pdfUrl)) {
                    error_log('LOA SUBMISSION - Updating PDF for existing submission ID: ' . $existingSubmission->getId() . ' from ' . $pdfUrl);
                    
                    $tmpDir = sys_get_temp_dir();
                    $tmpFilePath = tempnam($tmpDir, 'loa_pdf_update');

                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $pdfUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                    $fileData = curl_exec($ch);

                    if (curl_errno($ch)) {
                        $errorMsg = curl_error($ch);
                        curl_close($ch);
                        throw new \Exception('Failed to download replacement PDF: ' . $errorMsg);
                    }

                    $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);

                    if ($httpStatusCode !== 200 || empty($fileData)) {
                        throw new \Exception('Failed to download replacement PDF, HTTP status code: ' . $httpStatusCode);
                    }

                    file_put_contents($tmpFilePath, $fileData);
                    error_log('LOA SUBMISSION - Replacement PDF downloaded to: ' . $tmpFilePath);

                    // Cari Genre ID untuk "Article Text"
                    $genreDao = DAORegistry::getDAO('GenreDAO');
                    $genre = $genreDao->getByKey('SUBMISSION', $journal->getId());
                    $genreId = null;
                    if ($genre) {
                        $genreId = $genre->getId();
                    } else {
                        $genres = $genreDao->getByContextId($journal->getId());
                        while ($g = $genres->next()) {
                            if (!$g->getDependent()) {
                                $genreId = $g->getId();
                                break;
                            }
                        }
                    }

                    if (!$genreId) {
                        throw new \Exception('No suitable genre found for Article Text');
                    }

                    // Tambah file fisik ke OJS File Service
                    $originalFileName = basename(parse_url($pdfUrl, PHP_URL_PATH));
                    if (empty($originalFileName) || !str_ends_with(strtolower($originalFileName), '.pdf')) {
                        $originalFileName = 'article.pdf';
                    }
                    $cleanFileName = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $originalFileName);
                    $destinationFilename = uniqid() . '_' . $cleanFileName;

                    $fileId = \APP\core\Services::get('file')->add($tmpFilePath, $destinationFilename);
                    if (!$fileId) {
                        throw new \Exception('Failed to store physical replacement PDF in OJS file service');
                    }
                    error_log('LOA SUBMISSION - Replacement PDF stored in OJS file service, File ID: ' . $fileId);

                    // Daftarkan SubmissionFile baru di DB
                    $uploaderUserId = $existingSubmission->getData('userId') ?: 1;
                    $submissionFile = Repo::submissionFile()->newDataObject([
                        'fileId' => $fileId,
                        'submissionId' => $existingSubmission->getId(),
                        'uploaderUserId' => $uploaderUserId,
                        'genreId' => $genreId,
                        'locale' => $locale,
                        'fileStage' => \PKP\submissionFile\SubmissionFile::SUBMISSION_FILE_PROOF,
                    ]);
                    $savedSubmissionFile = Repo::submissionFile()->add($submissionFile);
                    $submissionFileId = is_numeric($savedSubmissionFile) ? $savedSubmissionFile : ($savedSubmissionFile ? $savedSubmissionFile->getId() : $submissionFile->getId());

                    if (!$submissionFileId) {
                        throw new \Exception('Failed to persist replacement SubmissionFile in database');
                    }
                    error_log('LOA SUBMISSION - Replacement SubmissionFile registered, ID: ' . $submissionFileId);

                    // Cari Galley PDF yang sudah ada
                    $existingGalleys = Repo::galley()->getCollector()
                        ->filterByPublicationIds([$publicationId])
                        ->getMany();

                    $galleyUpdated = false;
                    foreach ($existingGalleys as $galley) {
                        $label = strtoupper(trim($galley->getLabel()));
                        if ($label === 'PDF' || empty($label)) {
                            $oldSubmissionFileId = $galley->getData('submissionFileId');
                            
                            Repo::galley()->edit($galley, [
                                'submissionFileId' => $submissionFileId,
                            ]);
                            $galleyUpdated = true;
                            error_log('LOA SUBMISSION - Updated existing PDF Galley ID: ' . $galley->getId() . ' with SubmissionFile ID: ' . $submissionFileId);

                            // Bersihkan file SubmissionFile lama jika berbeda
                            if ($oldSubmissionFileId && $oldSubmissionFileId != $submissionFileId) {
                                try {
                                    $oldSubFile = Repo::submissionFile()->get($oldSubmissionFileId);
                                    if ($oldSubFile) {
                                        Repo::submissionFile()->delete($oldSubFile);
                                    }
                                } catch (\Throwable $e) {
                                    error_log('LOA SUBMISSION WARNING - Failed to delete old submission file: ' . $e->getMessage());
                                }
                            }
                            break;
                        }
                    }

                    if (!$galleyUpdated) {
                        $newGalley = Repo::galley()->newDataObject([
                            'publicationId' => $publicationId,
                            'label' => 'PDF',
                            'locale' => $locale,
                            'submissionFileId' => $submissionFileId,
                        ]);
                        $savedGalley = Repo::galley()->add($newGalley);
                        error_log('LOA SUBMISSION - Created new PDF Galley for existing submission, Galley ID: ' . (is_numeric($savedGalley) ? $savedGalley : $savedGalley->getId()));
                    }
                }
                
                // Fetch DOI again to return
                $doi = null;
                if (!empty($customDoi)) {
                    $newPub = Repo::publication()->get($publicationId);
                    $doiObject = $newPub->getData('doiObject');
                    if ($doiObject) {
                        $doi = $doiObject->getData('doi');
                    } else {
                        $doi = $customDoi;
                    }
                }

                $articleUrl = $this->getArticleViewUrl($journal, $existingSubmission->getId());
                $this->sendJson(true, 'Submission updated successfully', 200, [
                    'submission_id' => $existingSubmission->getId(),
                    'publication_id' => $publicationId,
                    'article_url' => $articleUrl,
                    'doi' => $doi,
                    'volume' => $payload['volume'] ?? '',
                ]);
            }

            if (!$request->isPost()) {
                $this->sendJson(false, 'Method Not Allowed', 405);
            }

            // 3. Validasi Secret Key berdasarkan setting jurnal target
            $secret = $payload['secret'] ?? null;
            $configuredSecret = $this->plugin->getSetting($journal->getId(), 'secretKey') ?: 'loacibkeyryudevs';
            if ($secret !== $configuredSecret) {
                error_log('LOA SUBMISSION ERROR - Invalid secret key for journal: ' . $journalPath);
                $this->sendJson(false, 'Unauthorized: Invalid secret key', 401);
            }

            // 4. Cari Section aktif pertama dari jurnal tujuan
            $sections = Repo::section()
                ->getCollector()
                ->filterByContextIds([$journal->getId()])
                ->getMany();

            $sectionId = null;
            foreach ($sections as $section) {
                $sectionId = $section->getId();
                break;
            }

            if (!$sectionId) {
                error_log('LOA SUBMISSION ERROR - No active section found for journal: ' . $journalPath);
                $this->sendJson(false, 'No active section found for this journal', 400);
            }
            error_log('LOA SUBMISSION - Section resolved: ' . $sectionId);

            // 5. Cari default Author User Group
            $authorUserGroups = Repo::userGroup()
                ->getCollector()
                ->filterByContextIds([$journal->getId()])
                ->filterByRoleIds([Role::ROLE_ID_AUTHOR])
                ->getMany();

            $authorUserGroupId = null;
            foreach ($authorUserGroups as $ug) {
                $authorUserGroupId = $ug->getId();
                break;
            }

            if (!$authorUserGroupId) {
                error_log('LOA SUBMISSION ERROR - No Author user group found');
                $this->sendJson(false, 'No Author user group found for this journal', 500);
            }
            error_log('LOA SUBMISSION - Author User Group ID resolved: ' . $authorUserGroupId);

            // 6. Ambil primary locale jurnal
            $locale = $journal->getPrimaryLocale() ?: 'en';
            error_log('LOA SUBMISSION - Primary Locale: ' . $locale);

            // Pecah author_name dari payload secara aman (bisa array, JSON string, atau string koma)
            $authorNamesRaw = $payload['author_name'] ?? '';
            $authorNames = [];
            if (is_array($authorNamesRaw)) {
                $authorNames = array_map('trim', $authorNamesRaw);
            } elseif (is_string($authorNamesRaw) && !empty($authorNamesRaw)) {
                $decoded = json_decode($authorNamesRaw, true);
                if (is_array($decoded)) {
                    $authorNames = array_map('trim', $decoded);
                } else {
                    $authorNames = array_map('trim', explode(',', $authorNamesRaw));
                }
            }
            $authorNames = array_filter($authorNames);
            if (empty($authorNames)) {
                $this->sendJson(false, 'Missing author_name parameter', 400);
            }

            // 7. Resolusi Pengguna OJS (Submitter / Owner) berdasarkan email form LOA
            $submitterEmail = $payload['email'] ?? null;
            if (empty($submitterEmail)) {
                $this->sendJson(false, 'Missing email parameter', 400);
            }

            $existingUser = DB::table('users')->where('email', $submitterEmail)->first();
            
            $submitterId = null;
            $ojsUsername = null;
            $ojsPassword = null;
            
            if ($existingUser) {
                $submitterId = $existingUser->user_id;
                $ojsUsername = $existingUser->username;
                
                // Memastikan user terhubung ke grup Author jurnal target di tabel user_user_groups
                $linkExists = DB::table('user_user_groups')
                    ->where('user_id', $submitterId)
                    ->where('user_group_id', $authorUserGroupId)
                    ->exists();
                if (!$linkExists) {
                    DB::table('user_user_groups')->insert([
                        'user_id' => $submitterId,
                        'user_group_id' => $authorUserGroupId,
                    ]);
                }
                error_log('LOA SUBMISSION - Submitter resolved to existing user: ' . $ojsUsername . ' (ID: ' . $submitterId . ')');
            } else {
                // Pengguna belum ada, buat akun baru
                // 1. Generate username unik
                $usernameBase = strstr($submitterEmail, '@', true); // bagian sebelum @
                $usernameBase = preg_replace('/[^a-zA-Z0-9]/', '', $usernameBase); // bersihkan karakter khusus
                $usernameBase = strtolower($usernameBase);
                if (empty($usernameBase)) {
                    $usernameBase = 'author';
                }
                
                $username = $usernameBase;
                $counter = 1;
                while (DB::table('users')->where('username', $username)->exists()) {
                    $username = $usernameBase . $counter;
                    $counter++;
                }
                
                // 2. Generate password acak
                $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#%';
                $ojsPassword = substr(str_shuffle($chars), 0, 10);
                
                // 3. Pecah nama depan & belakang dari author_name utama
                $primaryAuthorName = !empty($authorNames) ? trim(reset($authorNames)) : 'Author';
                $nameParts = explode(' ', $primaryAuthorName);
                $givenName = '';
                $familyName = '';
                if (count($nameParts) > 1) {
                    $familyName = array_pop($nameParts);
                    $givenName = implode(' ', $nameParts);
                } else {
                    $givenName = $primaryAuthorName;
                }
                
                // 4. Buat objek User baru di OJS
                $newUser = Repo::user()->newDataObject();
                $newUser->setUsername($username);
                $newUser->setEmail($submitterEmail);
                $newUser->setPassword(Validation::encryptCredentials($username, $ojsPassword));
                $newUser->setGivenName($givenName, $locale);
                $newUser->setFamilyName($familyName, $locale);
                $newUser->setDisabled(false);
                $newUser->setDateRegistered(Core::getCurrentDate());
                
                $newUserId = Repo::user()->add($newUser);
                if (!$newUserId) {
                    $this->sendJson(false, 'Failed to create new user in OJS', 500);
                }
                
                // 5. Hubungkan user baru ke grup Author jurnal target
                DB::table('user_user_groups')->insert([
                    'user_id' => $newUserId,
                    'user_group_id' => $authorUserGroupId,
                ]);
                
                $submitterId = $newUserId;
                $ojsUsername = $username;
                error_log('LOA SUBMISSION - Submitter created as new user: ' . $ojsUsername . ' (ID: ' . $submitterId . ')');
            }

            // Pecah keywords dari payload secara aman (bisa array, JSON string, atau string koma)
            $keywordsRaw = $payload['keywords'] ?? '';
            $keywordsArray = [];
            if (is_array($keywordsRaw)) {
                $keywordsArray = array_map('trim', $keywordsRaw);
            } elseif (is_string($keywordsRaw) && !empty($keywordsRaw)) {
                $decoded = json_decode($keywordsRaw, true);
                if (is_array($decoded)) {
                    $keywordsArray = array_map('trim', $decoded);
                } else {
                    $keywordsArray = array_map('trim', explode(',', $keywordsRaw));
                }
            }

            // 1. Download PDF dari pdf_url sebelum memulai DB transaction
            $pdfUrl = $payload['pdf_url'] ?? null;
            if (!empty($pdfUrl)) {
                error_log('LOA SUBMISSION - Downloading PDF from: ' . $pdfUrl);
                $tmpDir = sys_get_temp_dir();
                $tmpFilePath = tempnam($tmpDir, 'loa_pdf');

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $pdfUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_TIMEOUT, 60);
                $fileData = curl_exec($ch);

                if (curl_errno($ch)) {
                    $errorMsg = curl_error($ch);
                    curl_close($ch);
                    throw new \Exception('Failed to download PDF: ' . $errorMsg);
                }

                $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpStatusCode !== 200) {
                    throw new \Exception('Failed to download PDF, HTTP status code: ' . $httpStatusCode);
                }

                if (empty($fileData)) {
                    throw new \Exception('Downloaded PDF content is empty');
                }

                file_put_contents($tmpFilePath, $fileData);
                error_log('LOA SUBMISSION - PDF downloaded successfully to temp file: ' . $tmpFilePath);
            } else {
                error_log('LOA SUBMISSION ERROR - Missing pdf_url in payload');
                $this->sendJson(false, 'pdf_url is required', 400);
            }

            // Jalankan transaksi database
            $resultData = DB::transaction(function () use ($journal, $sectionId, $locale, $submitterId, $authorUserGroupId, $payload, $authorNames, $keywordsArray, $tmpFilePath, $pdfUrl, $ojsUsername, $ojsPassword) {
                // Buat Submission baru
                $submission = Repo::submission()->newDataObject([
                    'contextId' => $journal->getId(),
                    'sectionId' => $sectionId,
                    'locale' => $locale,
                    'userId' => $submitterId,
                    'userGroupId' => $authorUserGroupId,
                    'stageId' => 5, // WORKFLOW_STAGE_ID_PRODUCTION
                ]);

                // Buat Publication baru
                $publication = Repo::publication()->newDataObject([
                    'sectionId' => $sectionId,
                    'locale' => $locale,
                    'title' => [$locale => $payload['title'] ?? 'Untitled'],
                    'abstract' => [$locale => $payload['abstract'] ?? ''],
                    'keywords' => [$locale => $keywordsArray],
                    'loaNumber' => $payload['loa_number'] ?? '',
                    'loaDate' => $payload['loa_date'] ?? '',
                    'citationsRaw' => $payload['references'] ?? '',
                ]);

                // Simpan Submission dan Publication ke DB
                $subId = Repo::submission()->add($submission, $publication, $journal);

                // Buat stage assignment untuk menghubungkan user dengan submission sebagai Author
                DB::table('stage_assignments')->insert([
                    'submission_id' => $subId,
                    'user_group_id' => $authorUserGroupId,
                    'user_id' => $submitterId,
                    'date_assigned' => Core::getCurrentDate(),
                    'recommend_only' => 0,
                ]);

                // Muat objek submission terdaftar untuk mendapatkan publication id
                $newSubmission = Repo::submission()->get($subId);
                $newPublication = $newSubmission->getCurrentPublication();
                $publicationId = $newPublication->getId();

                // Save Custom DOI if provided
                $customDoi = $payload['doi'] ?? null;
                if (!empty($customDoi)) {
                    $doiId = DB::table('dois')->insertGetId([
                        'context_id' => $journal->getId(),
                        'doi' => $customDoi,
                        'status' => 2,
                    ]);
                    DB::table('publications')->where('publication_id', $publicationId)->update([
                        'doi_id' => $doiId,
                    ]);
                }

                // Buat Author baru (bisa multipel)
                $isPrimary = true;
                $authorCount = 0;

                // Cari tahu daftar nama penulis & instansi
                $authorsList = [];
                $authorsPayload = $payload['authors'] ?? [];

                if (is_array($authorsPayload) && !empty($authorsPayload)) {
                    foreach ($authorsPayload as $ap) {
                        $name = $ap['name'] ?? '';
                        $inst = $ap['institution'] ?? '';
                        if (!empty($name)) {
                            $authorsList[] = [
                                'name' => $name,
                                'institution' => $inst,
                            ];
                        }
                    }
                }

                // Fallback jika authorsList kosong
                if (empty($authorsList)) {
                    foreach ($authorNames as $name) {
                        $authorsList[] = [
                            'name' => $name,
                            'institution' => $payload['institution'] ?? '',
                        ];
                    }
                }

                foreach ($authorsList as $index => $authorItem) {
                    $name = $authorItem['name'];
                    $parts = explode(' ', $name);
                    $givenName = '';
                    $familyName = '';
                    if (count($parts) > 1) {
                        $familyName = array_pop($parts);
                        $givenName = implode(' ', $parts);
                    } else {
                        $givenName = $name;
                    }

                    $email = $payload['email'] ?? 'author@example.com';
                    if (!$isPrimary) {
                        $emailParts = explode('@', $email);
                        $emailDomain = count($emailParts) > 1 ? $emailParts[1] : 'example.com';
                        $emailLocal = count($emailParts) > 0 ? $emailParts[0] : 'author';
                        $email = $emailLocal . '+' . ($index + 1) . '@' . $emailDomain;
                    }

                    $author = Repo::author()->newDataObject([
                        'publicationId' => $publicationId,
                        'userGroupId' => $authorUserGroupId,
                        'email' => $email,
                        'givenName' => [$locale => $givenName],
                        'familyName' => [$locale => $familyName],
                        'affiliation' => [$locale => $authorItem['institution']],
                        'includeInBrowse' => true,
                        'primaryContact' => $isPrimary,
                    ]);
                    if ($isPrimary) {
                        $author->setData('userId', $submitterId);
                    }
                    Repo::author()->add($author);
                    $isPrimary = false;
                    $authorCount++;
                }
                error_log('LOA SUBMISSION - Created ' . $authorCount . ' authors in OJS');

                // --- PROSES SUBMISSION FILE PDF ---

                // 1. Cari Genre ID untuk "Article Text"
                $genreDao = DAORegistry::getDAO('GenreDAO');
                $genre = $genreDao->getByKey('SUBMISSION', $journal->getId());

                $genreId = null;
                if ($genre) {
                    $genreId = $genre->getId();
                } else {
                    // Fallback to first non-dependent document genre
                    $genres = $genreDao->getByContextId($journal->getId());
                    while ($g = $genres->next()) {
                        if (!$g->getDependent()) {
                            $genreId = $g->getId();
                            break;
                        }
                    }
                }

                if (!$genreId) {
                    throw new \Exception('No suitable genre found for Article Text');
                }
                error_log('LOA SUBMISSION - Resolved Genre ID: ' . $genreId);

                // 2. Tambah file fisik ke OJS File Service
                $originalFileName = basename(parse_url($pdfUrl, PHP_URL_PATH));
                if (empty($originalFileName) || !str_ends_with(strtolower($originalFileName), '.pdf')) {
                    $originalFileName = 'article.pdf';
                }
                $cleanFileName = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $originalFileName);
                $destinationFilename = uniqid() . '_' . $cleanFileName;

                $fileId = \APP\core\Services::get('file')->add($tmpFilePath, $destinationFilename);
                if (!$fileId) {
                    throw new \Exception('Failed to store physical PDF in OJS file service');
                }
                error_log('LOA SUBMISSION - PDF stored in OJS file service, File ID: ' . $fileId);

                // 3. Daftarkan SubmissionFile di DB
                $submissionFile = Repo::submissionFile()->newDataObject([
                    'fileId' => $fileId,
                    'submissionId' => $subId,
                    'uploaderUserId' => $submitterId,
                    'genreId' => $genreId,
                    'locale' => $locale,
                    'fileStage' => \PKP\submissionFile\SubmissionFile::SUBMISSION_FILE_PROOF,
                ]);
                $savedSubmissionFile = Repo::submissionFile()->add($submissionFile);
                $submissionFileId = is_numeric($savedSubmissionFile) ? $savedSubmissionFile : ($savedSubmissionFile ? $savedSubmissionFile->getId() : $submissionFile->getId());
                if (!$submissionFileId) {
                    throw new \Exception('Failed to persist SubmissionFile in database');
                }
                error_log('LOA SUBMISSION - SubmissionFile registered, SubmissionFile ID: ' . $submissionFileId);

                // 4. Buat PDF Galley
                $galley = Repo::galley()->newDataObject([
                    'publicationId' => $publicationId,
                    'label' => 'PDF',
                    'locale' => $locale,
                    'submissionFileId' => $submissionFileId,
                ]);
                $savedGalley = Repo::galley()->add($galley);
                $galleyId = is_numeric($savedGalley) ? $savedGalley : ($savedGalley ? $savedGalley->getId() : $galley->getId());
                if (!$galleyId) {
                    throw new \Exception('Failed to create Galley in database');
                }
                error_log('LOA SUBMISSION - PDF Galley created, Galley ID: ' . $galleyId);

                // --- PROSES PENENTUAN ISSUE & ASSIGNMENT ---

                $allIssues = Repo::issue()->getCollector()
                    ->filterByContextIds([$journal->getId()])
                    ->getMany();

                $publishedIssuesList = [];
                foreach ($allIssues as $issue) {
                    if ($issue->getPublished()) {
                        $publishedIssuesList[] = $issue;
                    }
                }

                $targetIssue = null;

                // 1. Coba cari issue yang sedang di-set sebagai "Current Issue"
                try {
                    $currentIssueCol = Repo::issue()->getCollector()
                        ->filterByContextIds([$journal->getId()])
                        ->filterByIsCurrent(true)
                        ->getMany();

                    if ($currentIssueCol && method_exists($currentIssueCol, 'first')) {
                        $targetIssue = $currentIssueCol->first();
                    } elseif ($currentIssueCol && (is_array($currentIssueCol) || $currentIssueCol instanceof \Countable) && count($currentIssueCol) > 0) {
                        $targetIssue = is_array($currentIssueCol) ? reset($currentIssueCol) : $currentIssueCol[0];
                    }
                } catch (\Throwable $e) {
                    error_log('LOA SUBMISSION WARNING - Failed to resolve current issue via filterByIsCurrent: ' . $e->getMessage());
                }

                // 2. Fallback: jika current issue tidak ditemukan, coba cari berdasarkan volume/number/year dari payload jika ada
                if (!$targetIssue) {
                    $payloadVolume = $payload['volume'] ?? null;
                    $payloadNumber = $payload['number'] ?? null;
                    $payloadYear = $payload['year'] ?? null;

                    if (!empty($payloadVolume) || !empty($payloadNumber) || !empty($payloadYear)) {
                        foreach ($publishedIssuesList as $issue) {
                            $match = true;
                            if (!empty($payloadVolume) && strval($issue->getVolume()) !== strval($payloadVolume)) {
                                $match = false;
                            }
                            if (!empty($payloadNumber) && strval($issue->getNumber()) !== strval($payloadNumber)) {
                                $match = false;
                            }
                            if (!empty($payloadYear) && strval($issue->getYear()) !== strval($payloadYear)) {
                                $match = false;
                            }
                            if ($match) {
                                $targetIssue = $issue;
                                break;
                            }
                        }
                    }
                }

                // 3. Fallback terakhir: ambil issue yang terakhir dipublikasikan
                if (!$targetIssue && !empty($publishedIssuesList)) {
                    usort($publishedIssuesList, function ($a, $b) {
                        $dateA = $a->getDatePublished() ? strtotime($a->getDatePublished()) : 0;
                        $dateB = $b->getDatePublished() ? strtotime($b->getDatePublished()) : 0;
                        return $dateB <=> $dateA;
                    });
                    $targetIssue = $publishedIssuesList[0];
                }

                if (!$targetIssue) {
                    throw new \Exception('No published issues found for this journal. Cannot auto-publish.');
                }
                error_log('LOA SUBMISSION - Target Issue resolved: ' . $targetIssue->getIssueIdentification() . ' (ID: ' . $targetIssue->getId() . ')');

                // 1. Tandai submission selesai diajukan (submit) - mengubah status ke STATUS_QUEUED
                Repo::submission()->submit($newSubmission, $journal);
                error_log('LOA SUBMISSION - Submission submitted successfully');

                // 2. Muat objek submission & publication terupdate
                $newSubmission = Repo::submission()->get($subId);
                $newPublication = $newSubmission->getCurrentPublication();

                // 3. Assign publication ke Issue
                $newPublication = Repo::publication()->edit($newPublication, [
                    'issueId' => $targetIssue->getId(),
                ]);
                error_log('LOA SUBMISSION - Publication assigned to Issue ID: ' . $targetIssue->getId());

                // 4. --- PROSES PUBLISH ---
                // publish() akan menetapkan status publication & submission ke STATUS_PUBLISHED (3)
                Repo::publication()->publish($newPublication);
                error_log('LOA SUBMISSION - Publication published successfully');

                // If payload requested no DOI, clean/remove OJS auto-generated DOI
                if (empty($payload['doi'])) {
                    DB::table('publications')->where('publication_id', $publicationId)->update([
                        'doi_id' => null,
                    ]);
                    error_log('LOA SUBMISSION - Removed auto-generated DOI because payload requested No DOI');
                }

                // Generate direct article URL
                $articleUrl = $this->getArticleViewUrl($journal, $subId);

                // Check DOI
                $doi = null;
                if (!empty($payload['doi'])) {
                    $newPublication = Repo::publication()->get($publicationId);
                    $doiObject = $newPublication->getData('doiObject');
                    if ($doiObject) {
                        $doi = $doiObject->getData('doi');
                    }
                }

                $volumeStr = '';
                if ($targetIssue) {
                    $volNum = $targetIssue->getVolume();
                    $issueNum = $targetIssue->getNumber();
                    $yearNum = $targetIssue->getYear();
                    
                    if ($volNum && $issueNum && $yearNum) {
                        $volumeStr = "Vol. {$volNum} No. {$issueNum} ({$yearNum})";
                    } else {
                        $volumeStr = $targetIssue->getIssueIdentification();
                    }
                }

                return [
                    'submission_id' => $subId,
                    'publication_id' => $publicationId,
                    'article_url' => $articleUrl,
                    'doi' => $doi,
                    'volume' => $volumeStr,
                    'ojs_username' => $ojsUsername,
                    'ojs_password' => $ojsPassword,
                ];
            });

            error_log('LOA SUBMISSION SUCCESS - Created Submission ID: ' . $resultData['submission_id']);
            $this->sendJson(true, 'Submission created successfully', 200, [
                'submission_id' => $resultData['submission_id'],
                'publication_id' => $resultData['publication_id'],
                'article_url' => $resultData['article_url'],
                'doi' => $resultData['doi'],
                'volume' => $resultData['volume'] ?? null,
                'ojs_username' => $resultData['ojs_username'] ?? null,
                'ojs_password' => $resultData['ojs_password'] ?? null,
            ]);

        } catch (\Throwable $e) {
            error_log('LOA SUBMISSION EXCEPTION - Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            $this->sendJson(false, 'Transaction failed: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(), 500);
        } finally {
            if ($tmpFilePath && file_exists($tmpFilePath)) {
                @unlink($tmpFilePath);
            }
        }
    }

    /**
     * Helper to retrieve JSON payload.
     */
    private function getJsonPayload(): array
    {
        $json = file_get_contents('php://input');
        return json_decode($json, true) ?: [];
    }

    /**
     * Construct direct article view URL instead of using OJS internal dispatcher which redirects to workflow
     */
    private function getArticleViewUrl($journal, $submissionId)
    {
        $request = \APP\core\Application::get()->getRequest();
        $baseUrl = rtrim($request->getBaseUrl(), '/');
        $journalPath = $journal->getPath();
        
        return $baseUrl . '/index.php/' . $journalPath . '/article/view/' . $submissionId;
    }

    /**
     * Log request payload to server log.
     */
    private function logRequest(string $endpoint, array $payload)
    {
        if (isset($payload['secret'])) {
            $payload['secret'] = '********';
        }

        error_log(json_encode([
            'plugin' => 'loaIntegration',
            'endpoint' => $endpoint,
            'payload' => $payload,
            'time' => date('Y-m-d H:i:s'),
        ]));
    }

    /**
     * Helper to send JSON response.
     */
    private function sendJson(bool $success, string $message, int $status = 200, array $extra = [])
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($status);

        $uuid = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );

        echo json_encode(array_merge([
            'success' => $success,
            'message' => $message,
            'request_id' => $uuid,
        ], $extra));
        exit;
    }
}
