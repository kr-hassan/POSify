# Email Troubleshooting Guide

## Current Email Configuration

Your `.env` file is configured with:
- **Email**: rabbi.qss@gmail.com
- **SMTP**: smtp.gmail.com
- **Port**: 587
- **Encryption**: TLS

## Important: Gmail App Password Required

**Gmail requires an App Password, NOT your regular password!**

### Steps to Get Gmail App Password:

1. **Enable 2-Step Verification** (if not already enabled):
   - Go to: https://myaccount.google.com/security
   - Enable "2-Step Verification"

2. **Generate App Password**:
   - Go to: https://myaccount.google.com/apppasswords
   - Select "Mail" and "Other (Custom name)"
   - Enter "POSify" as the name
   - Click "Generate"
   - Copy the 16-character password (no spaces)

3. **Update .env file**:
   - Replace `MAIL_PASSWORD=Dhaka@1230` with your new App Password
   - Example: `MAIL_PASSWORD=abcd efgh ijkl mnop` (remove spaces)

## Test Email Configuration

After updating the password, test it:

```bash
php artisan tinker
```

Then run:
```php
use Illuminate\Support\Facades\Mail;
Mail::raw('Test email from POSify', function($message) {
    $message->to('rabbi.qss@gmail.com')->subject('Test Email');
});
```

If successful, you'll see no errors. If it fails, check the error message.

## Check Email Logs

Check if emails are being attempted:
- View: `storage/logs/laravel.log`
- Look for lines containing "Attempting to send invoice email" or "Failed to send invoice email"

## Common Issues

### 1. "Authentication failed"
- **Solution**: Use App Password, not regular password
- Make sure 2-Step Verification is enabled

### 2. "Connection timeout"
- **Solution**: Check firewall/antivirus blocking port 587
- Try port 465 with SSL instead of TLS

### 3. "Email sent but not received"
- Check spam/junk folder
- Verify email address is correct
- Wait a few minutes (Gmail can delay)

### 4. "Email not sending for registered customers"
- Check if customer has email in database
- Check browser console for JavaScript errors
- Check Laravel logs for email errors

## Updated Email Logic

- **Registered customers with email**: Email sent automatically (no checkbox needed)
- **Walk-in customers**: Email sent only if checkbox is checked and email provided
- **Registered customers without email**: Email sent only if checkbox checked and email provided

## Debug Steps

1. **Check if email is being triggered**:
   - Look in `storage/logs/laravel.log` for "Attempting to send invoice email"

2. **Check email configuration**:
   ```bash
   php artisan config:show mail
   ```

3. **Test email directly**:
   ```bash
   php artisan tinker
   ```
   Then use the Mail::raw command above

4. **Check browser console**:
   - Open browser DevTools (F12)
   - Check Console tab for JavaScript errors
   - Check Network tab to see if request is being sent

## Next Steps

1. **Get Gmail App Password** (most important!)
2. **Update .env** with App Password
3. **Clear config cache**: `php artisan config:clear`
4. **Test with a sale** and check logs


