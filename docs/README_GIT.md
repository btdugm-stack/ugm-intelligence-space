# UGM Intelligence Space 🚀

**Dashboard Gateway & Decision Intelligence Portal**

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-8.3+-green.svg)](https://www.php.net/)
[![Status](https://img.shields.io/badge/status-Production%20Ready-brightgreen.svg)]()

═══════════════════════════════════════════════════════════════════════════════

## 📋 Overview

UGM Intelligence Space adalah portal katalog dashboard yang dirancang untuk Universitas Gadjah Mada (UGM). Aplikasi ini menyediakan antarmuka publik (read-only) dan admin (CRUD) dengan penyimpanan data berbasis JSON.

**Key Features:**
- 🎨 Responsive Design (Mobile, Tablet, Desktop)
- 🔐 Secure Authentication (Session-based)
- 📊 Dashboard Management (CRUD Operations)
- 🎯 Domain Intelligence Classification
- 📱 Mobile-First Approach
- 🌈 Modern UI with Background Image
- ⚡ Fast Performance (No Database Required)

═══════════════════════════════════════════════════════════════════════════════

## 🚀 Quick Start

### Prerequisites
- PHP 8.3+
- Apache Server
- Laragon / XAMPP / Wamp

### Installation

1. **Clone Repository**
```bash
git clone https://github.com/YOUR_USERNAME/ugm-intelligence-space.git
cd ugm-intelligence-space
```

2. **Place in Web Root**
```bash
# Laragon
C:\laragon\www\ugm-intelligence-space\

# XAMPP
C:\xampp\htdocs\ugm-intelligence-space\
```

3. **Start Server**
```bash
# Laragon
laragon start

# XAMPP
xampp_start.exe
```

4. **Access Application**
```
Public:  http://localhost/ugm-intelligence-space/
Admin:   http://localhost/ugm-intelligence-space/login.php
         Credentials: admin / admin123
```

═══════════════════════════════════════════════════════════════════════════════

## 📁 Project Structure

```
ugm-intelligence-space/
├── index.php                 # Public dashboard catalog
├── admin.php                 # Admin CRUD interface
├── detail.php                # Dashboard detail page
├── login.php                 # Authentication page
├── logout.php                # Logout handler
├── auth.php                  # Authentication guard
├── functions.php             # Core business logic (400+ lines)
├── bootstrap.php             # Application bootstrap
│
├── assets/
│   └── style.css             # Styling (302 lines, responsive)
│
├── data/
│   └── dashboards.json       # Dashboard metadata (flat array)
│
├── config/
│   └── environment.php       # Configuration settings
│
├── upload/
│   └── background.png        # Background image
│
├── logs/
│   ├── auth.log              # Authentication logs
│   ├── audit.log             # Audit trails
│   └── app.log               # Application logs
│
├── docs/
│   ├── BACKGROUND_IMAGE_APPLIED.md
│   ├── MOBILE_RESPONSIVE_DESIGN.md
│   ├── GIT_SETUP_AND_PUSH_GUIDE.md
│   ├── GIT_PUSH_STATUS_REPORT.md
│   └── ... (50+ documentation files)
│
├── tests/
│   └── (Test files and utilities)
│
├── backups/
│   └── (Database backups)
│
├── scripts/
│   └── (Utility scripts)
│
└── .env.example              # Environment template
```

═══════════════════════════════════════════════════════════════════════════════

## 🎨 Features

### Public Interface
- 📖 Dashboard Catalog (read-only)
- 🔍 Search & Filter
- 📊 Dashboard Details
- 🏷️ Domain Classification (10 types)
- 📍 Status Information
- 👥 Audience Segmentation

### Admin Interface
- ➕ Create Dashboards
- ✏️ Edit Dashboards
- 🗑️ Delete Dashboards
- 📋 View All Dashboards
- 🔐 Session-based Access Control
- 📝 JSON-based Data Storage
- 📊 Audit Logging

### Security
- ✅ Input Validation
- ✅ HTML Escaping (XSS Protection)
- ✅ CSRF Token Protection
- ✅ HTTP Security Headers
- ✅ Session Management (30-min timeout)
- ✅ Rate Limiting
- ✅ Audit Trail Logging

### Responsive Design
- 📱 Mobile (480px - 640px)
- 📱 Mobile Small (< 480px)
- 📱 Tablet (640px - 900px)
- 🖥️ Desktop (900px+)
- ✨ Fixed Navigation on Mobile
- 👆 Touch-Friendly UI (44x44 minimum)

═══════════════════════════════════════════════════════════════════════════════

## 🎯 Dashboard Domain Types

UGM Intelligence Space supports 10 dashboard domains:

1. **Executive** - Strategic indicators
2. **Academic** - Academic performance
3. **Research** - Research metrics
4. **Finance** - Financial indicators
5. **HR** - Human resources
6. **Student** - Student analytics
7. **Service** - Service quality
8. **Infrastructure** - System infrastructure
9. **Reputation** - University reputation
10. **Risk & Compliance** - Risk management

═══════════════════════════════════════════════════════════════════════════════

## 📊 Dashboard Status Types

- ✅ **Updated** - Recently updated (green)
- 📅 **Scheduled Update** - Upcoming update (yellow)
- ⚠️ **Need Review** - Requires attention (orange)
- ❌ **Delayed** - Update overdue (red)
- 🔧 **Maintenance** - Under maintenance (gray)

═══════════════════════════════════════════════════════════════════════════════

## 🔑 Default Credentials

```
Username: admin
Password: admin123
```

⚠️ **Warning:** Change credentials in production!

═══════════════════════════════════════════════════════════════════════════════

## 📱 Responsive Breakpoints

```css
/* Desktop (default) */
No media query needed

/* Tablet Medium - 900px and down */
@media(max-width:900px) { ... }

/* Tablet Small - 768px and down */
@media(max-width:768px) { ... }

/* Mobile - 640px and down (Fixed navbar) */
@media(max-width:640px) { ... }

/* Mobile Small - 480px and down */
@media(max-width:480px) { ... }
```

═══════════════════════════════════════════════════════════════════════════════

## 🎨 Color Palette

| Variable | Value | Purpose |
|----------|-------|---------|
| `--ugm-blue` | #0a5a8a | Primary brand color |
| `--cyan` | #20bfd4 | Accent color |
| `--gold` | #c9a961 | Secondary accent |
| `--green` | #2db89f | Success state |
| `--red` | #d97c7c | Error state |
| `--slate` | #3a5a6f | Text color |
| `--muted` | #738599 | Muted text |

═══════════════════════════════════════════════════════════════════════════════

## 📖 API Data Structure

### Dashboard Object
```json
{
  "id": "unique-id",
  "name": "Dashboard Name",
  "description": "Dashboard description",
  "domain": "Executive",
  "owner": "Owner Name",
  "update_frequency": "Weekly",
  "last_updated": "2026-07-10",
  "status": "Updated",
  "access": "Public",
  "audience": "All Staff",
  "url": "https://example.com/dashboard",
  "tags": ["tag1", "tag2"]
}
```

═══════════════════════════════════════════════════════════════════════════════

## 🔒 Security Features

### Authentication
- Session-based login
- Password validation
- Session timeout (30 minutes)
- Secure cookies (HttpOnly)

### Data Protection
- HTML output escaping with `e()` function
- Input validation
- CSRF token protection
- SQL injection prevention (no database)

### Audit Logging
- Authentication logs
- Admin action logs
- Error tracking
- Timestamp recording

### HTTP Security
- X-Frame-Options: SAMEORIGIN
- X-Content-Type-Options: nosniff
- X-XSS-Protection: 1; mode=block
- Strict-Transport-Security

═══════════════════════════════════════════════════════════════════════════════

## 🧪 Testing

### Browser DevTools Testing
1. Press `F12` to open DevTools
2. Click Device Toolbar icon (Ctrl+Shift+M)
3. Test breakpoints: 900px, 768px, 640px, 480px
4. Verify mobile & desktop layouts

### Credentials for Testing
```
Admin Account:
- Username: admin
- Password: admin123

Test Dashboard Access:
- Public dashboards visible to all
- Admin can see all dashboards
```

═══════════════════════════════════════════════════════════════════════════════

## 📚 Documentation

Complete documentation available in `docs/` folder:

- **BACKGROUND_IMAGE_APPLIED.md** - Background image implementation
- **MOBILE_RESPONSIVE_DESIGN.md** - Responsive design details
- **GIT_SETUP_AND_PUSH_GUIDE.md** - Git setup instructions
- **GIT_PUSH_STATUS_REPORT.md** - Push status report
- **START_GUIDE.md** - Getting started guide
- **PROJECT_STRUCTURE.md** - Project structure overview
- **TROUBLESHOOTING_GUIDE.md** - Problem solving guide
- Plus 40+ additional documentation files

═══════════════════════════════════════════════════════════════════════════════

## 🚀 Deployment

### Production Setup

1. **Environment Configuration**
```bash
cp .env.example .env
# Update .env with production settings
```

2. **Security Hardening**
- Change default credentials
- Update admin credentials in `login.php`
- Enable HTTPS
- Set secure environment variables
- Review `.gitignore` before deployment

3. **Performance Optimization**
- Enable browser caching
- Minify CSS/JS
- Use CDN for assets
- Optimize background image

4. **Database Migration** (Future)
- Migrate from JSON to MySQL/PostgreSQL
- Implement ORM (Doctrine, Eloquent)
- Add caching layer
- Implement database backup strategy

═══════════════════════════════════════════════════════════════════════════════

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

═══════════════════════════════════════════════════════════════════════════════

## 👥 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'feat: Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

═══════════════════════════════════════════════════════════════════════════════

## 🐛 Bug Reports

Please report bugs by creating an issue with:
- Clear description
- Steps to reproduce
- Expected behavior
- Actual behavior
- Screenshots/logs

═══════════════════════════════════════════════════════════════════════════════

## 💡 Feature Requests

Feature requests are welcome! Please create an issue with:
- Feature description
- Use case explanation
- Proposed implementation (optional)
- Related issues/PRs

═══════════════════════════════════════════════════════════════════════════════

## 📞 Support

### Getting Help
- Check **docs/** folder for comprehensive guides
- Review **TROUBLESHOOTING_GUIDE.md** for common issues
- Check existing issues on GitHub
- Create new issue if problem persists

### Contact
- Email: dev@ugm-intelligence-space.local
- Issue Tracker: GitHub Issues
- Documentation: `/docs/` folder

═══════════════════════════════════════════════════════════════════════════════

## 🎉 Latest Updates (July 10, 2026)

### Version 1.0.0 - Initial Release ✅

**New Features:**
- ✅ Background image with parallax effect
- ✅ Updated color palette (cyan tone harmony)
- ✅ Clean hero section design
- ✅ Enhanced pulse-card styling
- ✅ Comprehensive mobile responsive design
  - 768px breakpoint (Tablet)
  - 640px breakpoint (Mobile - Fixed navbar)
  - 480px breakpoint (Small mobile)
- ✅ Git repository initialized
- ✅ Complete documentation suite

**Improvements:**
- Better visual hierarchy
- Improved mobile UX
- Touch-friendly interface
- Enhanced readability

═══════════════════════════════════════════════════════════════════════════════

## 🔮 Future Roadmap

- [ ] User authentication enhancement (SSO integration)
- [ ] Database migration (MySQL/PostgreSQL)
- [ ] Advanced analytics dashboard
- [ ] Data export functionality (PDF, Excel)
- [ ] Real-time notifications
- [ ] Dark mode support
- [ ] Multi-language support
- [ ] Advanced caching strategy
- [ ] API endpoint creation
- [ ] Mobile app development

═══════════════════════════════════════════════════════════════════════════════

## 📊 Statistics

- **Total Lines of Code:** 5000+
- **Total Documentation:** 3000+ lines
- **CSS Breakpoints:** 4 levels
- **Supported Domains:** 10 types
- **Dashboard Statuses:** 5 states
- **Security Features:** 8+ implementations

═══════════════════════════════════════════════════════════════════════════════

## ⭐ Acknowledgments

Built with ❤️ for UGM (Universitas Gadjah Mada)

- Design inspired by modern dashboard patterns
- Color scheme based on UGM branding guidelines
- Responsive design best practices
- Security best practices from OWASP

═══════════════════════════════════════════════════════════════════════════════

## 📄 Version History

```
v1.0.0 (2026-07-10) - Initial Release
- Background image implementation
- Mobile responsive design
- Git repository setup
- Complete documentation

v0.1.0 (2026-06-15) - POC Version
- Basic dashboard functionality
- Admin CRUD operations
- Public catalog interface
```

═══════════════════════════════════════════════════════════════════════════════

**Ready to use! Happy dashboard management! 🚀**

---

**Last Updated:** July 10, 2026  
**Maintained by:** GitHub Copilot  
**Repository Status:** ✅ Active & Maintained

═══════════════════════════════════════════════════════════════════════════════
