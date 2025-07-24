<?php
// includes/email_manager.php
// General email management utility for EduVault

require_once __DIR__ . '/../vendor/autoload.php';

class EmailManager
{
    private $mailer;
    private $templates;

    public function __construct()
    {
        $this->mailer = new PHPMailer\PHPMailer\PHPMailer(true);
        // Default SMTP config (customize as needed)
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.gmail.com';
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'samkitjain2809@gmail.com';
        $this->mailer->Password = getenv('secret');// use App Password if 2FA is on
        $this->mailer->SMTPSecure = 'tls'; // or 'ssl' for port 465
        $this->mailer->Port = 587; // or 465 for SSL
        $this->mailer->isHTML(true);
        $this->mailer->setFrom('samkitjain2809@gmail.com', 'EduVault Admin');

        // Predefined templates (add more as needed)
        $this->templates = [
            'welcome' => [
                'subject' => 'Welcome to EduVault, {{name}}!',
                'body' => '<p>Hi {{name}},</p><p>Thank you for joining EduVault. Start sharing and discovering educational resources today!</p>'
            ],
            'password_reset' => [
                'subject' => 'Password Reset Request',
                'body' => '<p>Hi {{name}},</p><p>Click <a href="{{reset_link}}">here</a> to reset your password.</p>'
            ],
            'admin_announcement' => [
                'subject' => 'Announcement from EduVault Admin',
                'body' => '<p>Dear {{name}},</p><p>{{message}}</p>'
            ],
            'file_approved' => [
                'subject' => 'Your file has been approved!',
                'body' => '<p>Hi {{name}},</p><p>Your file "{{file_title}}" has been approved and is now public.</p>'
            ],
            // Add more templates as needed
        ];
    }

    // Send an email using a template
    public function sendTemplate($to, $template, $vars = [])
    {
        if (!isset($this->templates[$template]))
            return false;
        $subject = $this->replaceVars($this->templates[$template]['subject'], $vars);
        $body = $this->replaceVars($this->templates[$template]['body'], $vars);
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->send();
            return true;
        } catch (Exception $e) {

            return false; // Optionally log error
        }
    }

    // Send a custom email (no template)
    public function sendCustom($to, $subject, $body)
    {
        try {
            $this->mailer->clearAddresses();
            $this->mailer->addAddress($to);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            // Optionally log error
            return false;
        }
    }

    // Add or update a template
    public function setTemplate($key, $subject, $body)
    {
        $this->templates[$key] = [
            'subject' => $subject,
            'body' => $body
        ];
    }

    // Get all templates
    public function getTemplates()
    {
        return $this->templates;
    }

    // Helper: replace {{var}} in template
    private function replaceVars($text, $vars)
    {
        foreach ($vars as $k => $v) {
            $text = str_replace('{{' . $k . '}}', htmlspecialchars($v), $text);
        }
        return $text;
    }
}