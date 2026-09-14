# Shortner v2

PHP + MySQL URL shortener for **one domain**. Separate admin + user panels.

Public repo: https://github.com/faham112/shortner-v2-public

## Features
- Custom short codes: `yourdomain.com/code`
- Link preview on/off
- Click analytics (browser, country, device, OS, referrer)
- Android Chrome app only sent to dump URL
- Other browsers follow real destination
- After 5 minutes, 3-second hop to admin URL then continue
- Admin creates users (name, email, password)
- Single-domain license + install lock
- Dark/light + English/Urdu
- Email/password + remember me
- CSRF, PDO, password hashing

## Hostinger install
1. Create MySQL database in hPanel
2. Upload files to public_html
3. Open `https://your-domain.com/install`
4. Fill DB + exact domain + admin login
5. After install, `install.lock` is created. Keep `config.php` off GitHub.

## Settings
Admin → Settings: global dump URL, hop URL, minutes (5), seconds (3).

## License
Bound to install domain. Other domain = 403.
