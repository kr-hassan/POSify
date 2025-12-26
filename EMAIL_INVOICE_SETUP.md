# Email Invoice Setup Guide

## Overview
Your POS system now supports sending invoices via email to customers automatically.

## How It Works

### For Registered Customers:
- **If customer has email**: Invoice is **automatically sent** to their registered email address
- **If customer has no email**: Email field appears for optional entry

### For Walk-in Customers:
- Email field appears for **optional entry**
- If email is provided, invoice will be sent
- If no email provided, invoice is only printed (no email sent)

## Setup Instructions

### 1. Configure Mail Settings

Update your `.env` file with your email configuration:

#### For Gmail:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

**Note for Gmail**: You need to use an [App Password](https://support.google.com/accounts/answer/185833) instead of your regular password.

#### For Other SMTP Servers:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server.com
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourstore.com
MAIL_FROM_NAME="Your Store Name"
```

### 2. Test Email Configuration

Run this command to test your email setup:
```bash
php artisan tinker
```

Then in tinker:
```php
Mail::raw('Test email', function ($message) {
    $message->to('your-test-email@example.com')
            ->subject('Test Email');
});
```

### 3. Using Email Feature in POS

1. **For Registered Customers with Email**:
   - Select customer from dropdown
   - Complete sale
   - Invoice automatically sent to customer's email

2. **For Registered Customers without Email**:
   - Select customer from dropdown
   - Email field appears
   - Enter email (optional)
   - Complete sale
   - Invoice sent if email provided

3. **For Walk-in Customers**:
   - Leave customer as "Walk-in Customer"
   - Email field appears
   - Enter email (optional)
   - Complete sale
   - Invoice sent if email provided

## Email Template

The invoice email includes:
- Shop information (name, address, phone, email)
- Invoice number and date
- Customer information
- Itemized list of products
- Subtotal, tax, discount
- Total amount
- Payment method and change
- Professional HTML formatting

## Troubleshooting

### Emails Not Sending

1. **Check .env configuration**: Make sure all mail settings are correct
2. **Check logs**: View `storage/logs/laravel.log` for email errors
3. **Test SMTP connection**: Use tinker to test email sending
4. **Check spam folder**: Customer emails might be in spam
5. **Verify credentials**: Double-check username/password

### Common Issues

**Gmail "Less secure app" error**:
- Use App Password instead of regular password
- Enable 2-factor authentication
- Generate App Password from Google Account settings

**Connection timeout**:
- Check firewall settings
- Verify SMTP port (587 for TLS, 465 for SSL)
- Try different encryption (tls vs ssl)

**Email sent but not received**:
- Check spam/junk folder
- Verify email address is correct
- Check email server logs

## Features

✅ Automatic email for customers with email addresses
✅ Optional email for walk-in customers
✅ Professional HTML email template
✅ Error handling (sale completes even if email fails)
✅ Success notification shows if email was sent
✅ Email address displayed in success message

## Notes

- Email sending happens **after** sale is completed
- If email fails, sale still completes successfully (error is logged)
- Email is sent asynchronously (doesn't slow down sale process)
- Invoice PDF attachment can be added in future updates


