<?php
use BeyondCode\Mailbox\Facades\Mailbox;
use BeyondCode\Mailbox\InboundEmail;

Mailbox::to('support@yourdomain.com', function (InboundEmail $email) {
// Access email details
$from = $email->from();    // Sender's email address
$subject = $email->subject(); // Subject
$body = $email->text();    // Body text

// Process email (e.g., save to database or trigger a notification)
Log::info("Email received from: $from, Subject: $subject");
});
