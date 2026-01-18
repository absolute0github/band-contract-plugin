# Skinny Moo Contract Builder - WordPress Plugin

## Overview
WordPress plugin for skinnymoo.com to create, send, and manage performance agreements and invoices for the band "Skinny Moo" with digital signing capabilities.

## GitHub Repository
- **Repo:** https://github.com/absolute0github/band-contract-plugin
- **Branch:** main
- **Current Version:** 1.0.10

## Key Commands
```bash
# Commit and push
git add . && git commit -m "message" && git push origin main

# Create GitHub release (replace TOKEN and version)
curl -X POST -H "Authorization: token TOKEN" -H "Accept: application/vnd.github.v3+json" \
  https://api.github.com/repos/absolute0github/band-contract-plugin/releases \
  -d '{"tag_name": "vX.X.X", "name": "vX.X.X - Title", "body": "Release notes"}'
```

## File Structure
```
skinny-moo-contract-builder/
├── skinny-moo-contract-builder.php    # Main plugin file, version defined here
├── includes/
│   ├── class-smcb-activator.php       # DB table creation & upgrades
│   ├── class-smcb-contract.php        # Contract model (CRUD, payments)
│   ├── class-smcb-token-manager.php   # Secure link tokens
│   ├── class-smcb-email.php           # Email handling
│   └── class-smcb-pdf-generator.php   # PDF generation (TCPDF)
├── admin/
│   ├── class-smcb-admin.php           # Admin functionality & AJAX handlers
│   ├── partials/
│   │   ├── contracts-list.php         # Contract list view
│   │   ├── contract-view.php          # Single contract view (has payment UI)
│   │   └── contract-form.php          # Create/edit contract form
│   ├── css/smcb-admin.css
│   └── js/smcb-admin.js
├── public/
│   ├── class-smcb-public.php          # Public contract view handler
│   ├── partials/contract-view.php     # Client-facing contract page
│   ├── css/smcb-public.css
│   └── js/smcb-public.js              # Signature pad handling
├── api/
│   └── class-smcb-rest-api.php        # REST endpoints for signing
├── templates/
│   ├── pdf/                           # PDF templates (not used, inline in generator)
│   └── email/
│       ├── contract-sent.php          # Initial email to client
│       ├── signature-confirmation.php # After client signs
│       ├── admin-notification.php     # Notify admin of signature
│       └── payment-receipt.php        # Payment receipt to client
└── vendor/
    └── plugin-update-checker/         # GitHub update integration
```

## Database Tables
- `wp_smcb_contracts` - Main contract data, signatures, payment tracking
- `wp_smcb_invoice_line_items` - Invoice line items
- `wp_smcb_contract_activity_log` - Audit trail

## Key Classes

### SMCB_Contract (includes/class-smcb-contract.php)
- `create($data)` - Create new contract
- `update($id, $data)` - Update contract
- `get($id)` - Get contract by ID
- `get_by_token($token)` - Get contract by access token
- `record_signature($id, $signature, $name, $ip)` - Record client signature
- `record_payment($id, $type, $data)` - Record deposit/balance payment

### SMCB_Email (includes/class-smcb-email.php)
- `send_contract()` - Send contract link to client
- `send_signature_confirmation($attachments)` - Send signed confirmation
- `send_admin_notification()` - Notify admin of signature
- `send_payment_receipt($type, $amount, $method)` - Send payment receipt

### SMCB_PDF_Generator (includes/class-smcb-pdf-generator.php)
- `generate_all($signed)` - Generate all PDFs
- `generate_cover_letter()` - Cover letter PDF
- `generate_contract($signed)` - Contract/agreement PDF
- `generate_invoice()` - Invoice PDF

## Test Mode
- Settings → Test Mode checkbox
- When enabled, all emails go to the test email address instead of client
- Subject lines prefixed with [TEST]

## Contract Workflow
1. Admin creates contract in WordPress
2. System generates contract number (SM-YYYY-XXXX) and access token
3. Admin sends contract → client receives email with secure link
4. Client views contract, signs with signature pad
5. System generates signed PDFs, sends confirmations
6. Admin records payments → receipts sent to client

## Company Info (Hardcoded in main plugin file)
- Skinny Moo Media Services, LLC
- 4448 Luckystone Way, Medina, Ohio 44256
- Phone: (330) 421-1960
- Email: booking@skinnymoo.com
- EIN: 20-4746552

## Version History
- **1.0.10** - Fixed database upgrade not running on plugin update
- **1.0.9** - Added payment tracking with receipt emails
- **1.0.8** - Phase 1 & 2 content/layout updates (labels, PDF improvements, admin email cards)
- **1.0.7** - Signature layout (performer left, client right)
- **1.0.6** - Full width logos everywhere
- **1.0.5** - Email and invoice PDF improvements
- **1.0.4** - Test mode functionality

## Pending Work (see IMPLEMENTATION-PLAN.md)

### Phase 3: Image Upload Feature
- Allow client to upload up to 4 images (load-in location, performance area)
- Drag & drop functionality
- Store in uploads/smcb-contracts/images/
- Display in admin and optionally in PDFs

### Phase 4: Square Payment Integration
- Payment method selection on public contract view (check, cash, card)
- Square API integration for credit card payments
- Fee calculation (3.5% + $0.25)
- Payment links via email
- Webhook handling for payment confirmation

## When Updating Version
1. Update version in header comment: `* Version: X.X.X`
2. Update version constant: `define( 'SMCB_VERSION', 'X.X.X' );`
3. If database changes needed, add upgrade function in class-smcb-activator.php
4. Commit with: `Co-Authored-By: Claude Opus 4.5 <noreply@anthropic.com>`
5. Push to main
6. Create GitHub release

## Common Issues
- **"Failed to record payment"** - Database columns missing, deactivate/reactivate plugin or update to latest version
- **Emails not sending** - Check test mode settings, verify SMTP configured
- **PDFs not generating** - Check TCPDF is loaded, verify write permissions on uploads folder
