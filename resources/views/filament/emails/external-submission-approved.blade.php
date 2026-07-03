<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Approved</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f9fafb;">

    <!-- Main container with inline CSS -->
    <div style="max-width: 600px; margin: 30px auto; padding: 30px; background-color: #fff; border: 1px solid #e5e7eb; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05);">
        
        <!-- Header with accent border (No logo) -->
        <div style="border-left: 8px solid #1e3a8a; padding-left: 20px; margin-bottom: 30px; margin-top: 10px;">
            <h1 style="font-size: 22px; font-weight: bold; color: #1f2937; margin: 0;">Congratulations! Your Submission Has Been Approved</h1>
        </div>

        <!-- Greeting -->
        <p style="font-size: 16px; color: #4b5563; margin-bottom: 15px;">Dear {{ $submission->author_name }},</p>

        <!-- Approval message -->
        <div style="background-color: #eff6ff; padding: 15px; border-radius: 8px; margin-bottom: 25px; border-left: 4px solid #2563eb; color: #475569;">
            <p style="margin: 0;">We are pleased to inform you that your Letter of Acceptance (LOA) request has been approved by the editorial team.</p>
        </div>

        <!-- Detail Section -->
        <h2 style="font-size: 18px; font-weight: bold; color: #1f2937; border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; margin-bottom: 15px;">Submission Details:</h2>
        
        <!-- Card-like details table -->
        <div style="background-color: #f9fafb; border-radius: 8px; padding: 15px; margin-bottom: 25px;">
            <table role="presentation" style="width: 100%; border-collapse: collapse; border-spacing: 0;">
                <tr>
                    <td style="padding: 8px 0; vertical-align: top; width: 140px; font-weight: 600; color: #1f2937;">Title:</td>
                    <td style="padding: 8px 0; color: #4b5563;">{{ $submission->title }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; vertical-align: top; font-weight: 600; color: #1f2937;">Author Name:</td>
                    <td style="padding: 8px 0; color: #4b5563;">{{ $submission->author_name }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; vertical-align: top; font-weight: 600; color: #1f2937;">Journal:</td>
                    <td style="padding: 8px 0; color: #4b5563;">{{ $submission->journal->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; vertical-align: top; font-weight: 600; color: #1f2937;">Volume/Issue:</td>
                    <td style="padding: 8px 0; color: #4b5563;">{{ $submission->volume }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; vertical-align: top; font-weight: 600; color: #1f2937;">LOA Date:</td>
                    <td style="padding: 8px 0; color: #4b5563;">{{ $submission->date_of_loa->format('F d, Y') }}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; vertical-align: top; font-weight: 600; color: #1f2937;">Approval Date:</td>
                    <td style="padding: 8px 0; color: #4b5563;">{{ $submission->approved_date->format('F d, Y') }}</td>
                </tr>
            </table>
        </div>

        <!-- Download/Print info -->
        <p style="color: #4b5563; margin-bottom: 20px;">You can view and download your Letter of Acceptance (LOA) directly using the link below:</p>

        <!-- Buttons container -->
        <div style="margin-bottom: 30px;">
            <a href="{{ url('/loa/preview/' . $submission->id) }}" 
               style="display: inline-block; padding: 12px 24px; background-color: #2563eb; color: white; text-decoration: none; font-weight: 600; border-radius: 6px; margin-right: 10px; margin-bottom: 10px; transition: background-color 0.2s;">Letter of Acceptance (LOA)</a>
        </div>

        <!-- Closing remarks -->
        <p style="color: #4b5563; margin-bottom: 15px;">Thank you for choosing to publish with us.</p>

        <!-- Signature -->
        <div style="margin-top: 25px; padding-top: 15px; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 14px;">
            <p style="margin: 0;">Sincerely,<br>Editorial Team</p>
        </div>
    </div>

</body>
</html>
