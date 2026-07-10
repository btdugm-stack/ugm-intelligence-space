# UGM Intelligence Space - Proof of Concept

PoC landing page kumpulan dashboard analytics dengan 2 tampilan:

1. **Tampilan User / Publik**
   - Tanpa login
   - Melihat dashboard berstatus `Public`
   - Search dashboard
   - Filter domain dan status
   - Featured dashboard
   - Detail dashboard

2. **Tampilan Admin**
   - Login admin
   - CRUD konten dashboard
   - Mengatur domain, owner, status, akses, last updated, dan URL dashboard
   - Data tersimpan di `data/dashboards.json`

## Cara Deploy di Laragon / XAMPP

1. Extract folder `ugm-intelligence-space-poc`.
2. Copy folder ke:
   - Laragon: `C:/laragon/www/`
   - XAMPP: `C:/xampp/htdocs/`
3. Jalankan Apache.
4. Buka:
   - Public: `http://localhost/ugm-intelligence-space-poc/`
   - Admin: `http://localhost/ugm-intelligence-space-poc/login.php`

## Login Admin Demo

- Username: `admin`
- Password: `admin123`

## Catatan Pengembangan Lanjutan

Untuk production, disarankan:
- Login menggunakan SSO UGM / OIDC.
- Data dashboard disimpan di database MySQL/PostgreSQL.
- Role-based access control.
- Audit trail perubahan konten.
- Integrasi metadata dashboard dari BI tools.
- Data dictionary dan governance workflow.
