<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registration Confirmation</title>
        <style>
            body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f8f9fa; }
            .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 30px; text-align: center; }
            .content { padding: 40px 30px; }
            .discord-code { background: #f1f3f4; border: 2px dashed #667eea; border-radius: 8px; padding: 20px; text-align: center; margin: 20px 0; }
            .code { font-family: 'Courier New', monospace; font-size: 24px; font-weight: bold; color: #667eea; letter-spacing: 2px; }
            .rules { background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 20px 0; }
            .footer { background: #f8f9fa; padding: 30px; text-align: center; color: #6c757d; }
            .btn { display: inline-block; background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🎉 Registration Confirmed!</h1>
                <p>Welcome to the {{ $eventTitle }}, supporting {{ $orgName }}</p>
            </div>

            <div class="content">
                <h2>Hello {{ $registration->name }}!</h2>
                <p>Thank you for registering for {{ $eventTitle }}, supporting {{ $orgName }}. We're excited to have you join us!</p>

                <div class="discord-code">
                    <h3>Your Discord Access Code</h3>
                    <div class="code">{{ $registration->discord_code }}</div>
                    <p><small>Use this code to join our Discord server</small></p>
                </div>

                <div class="rules">
                    <h3>📋 Event Rules & Guidelines</h3>
                    <ul>
                        <li><strong>Respect:</strong> Treat all participants with kindness and respect</li>
                        <li><strong>Content:</strong> Keep all content appropriate and family-friendly</li>
                        <li><strong>Participation:</strong> Be active and engage positively with the community</li>
                        <li><strong>Technical:</strong> Test your equipment before the event starts</li>
                        <li><strong>Schedule:</strong> Stick to your assigned time slots if you're presenting</li>
                        <li><strong>Support:</strong> Remember we're raising funds for {{ $orgName }}</li>
                    </ul>
                </div>

                @if ($discordInviteLink)
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="{{ $discordInviteLink }}" class="btn">Join Discord Server</a>
                    </div>
                @endif

                <h3>📅 Event Details</h3>
                <ul>
                    <li><strong>Date:</strong> {{ $eventDateLabel }}</li>
                    <li><strong>Duration:</strong> 24 Hours</li>
                    <li><strong>Platform:</strong> Discord + Streaming Platform</li>
                    <li><strong>Cause:</strong> Supporting {{ $orgName }}</li>
                </ul>

                <p>If you have any questions, please don't hesitate to reach out to our team.</p>
            </div>

            <div class="footer">
                <p>This email was sent regarding your registration for {{ $eventTitle }}.</p>
                <p><strong>{{ $orgName }} Fundraising Team</strong></p>
            </div>
        </div>
    </body>
</html>
