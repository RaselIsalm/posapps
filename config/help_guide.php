<?php

/**
 * POS Help Guide — module map + bilingual FAQ (EN / BN).
 * Used by HelpGuideController and the interactive UI tour.
 */
return [
    'getting_started' => [
        'en' => [
            'title' => 'Getting Started (Recommended Order)',
            'steps' => [
                'Settings → Branches, Units, Currencies, Roles & Users, Timezone, Print layout',
                'Products → Categories, Brands, then Create Product (or Bulk Import)',
                'Peoples → Suppliers & Customers',
                'Accounts → Create Cash/Bank accounts',
                'Purchase → Add Purchase (status Received) to bring stock in',
                'Sale → Quick Sale to sell, then check Sale List / Invoice',
                'Reports → Summary & Inventory to verify numbers',
            ],
        ],
        'bn' => [
            'title' => 'শুরু করুন (সঠিক অর্ডার)',
            'steps' => [
                'Settings → ব্রাঞ্চ, ইউনিট, কারেন্সি, ভূমিকা ও ইউজার, টাইমজোন, প্রিন্ট লেআউট',
                'Products → ক্যাটাগরি, ব্র্যান্ড, তারপর প্রোডাক্ট তৈরি (বা Bulk Import)',
                'Peoples → সাপ্লায়ার ও কাস্টমার',
                'Accounts → ক্যাশ/ব্যাংক অ্যাকাউন্ট তৈরি',
                'Purchase → ক্রয় যোগ করুন (Received) যাতে স্টক আসে',
                'Sale → Quick Sale দিয়ে বিক্রি, Sale List / Invoice চেক করুন',
                'Reports → Summary ও Inventory দিয়ে হিসাব মিলান',
            ],
        ],
    ],

    'modules' => [
        [
            'id' => 'dashboard-menu',
            'icon' => 'fa-house',
            'route' => 'dashboard',
            'en' => [
                'title' => 'Dashboard',
                'where' => 'Left sidebar → Dashboard',
                'what' => 'Shows sales, purchases, expenses, profit, dues, cash in/out for Today / 7 days / Month / Year.',
                'how' => 'Open Dashboard and use the date filter buttons. Click cards/lists to jump into recent sales or purchases.',
            ],
            'bn' => [
                'title' => 'ড্যাশবোর্ড',
                'where' => 'বাম সাইডবার → Dashboard',
                'what' => 'বিক্রি, ক্রয়, খরচ, লাভ, বাকি, ক্যাশ ইন/আউট দেখায় (আজ / ৭ দিন / মাস / বছর)।',
                'how' => 'Dashboard খুলে ডেট ফিল্টার বাটন ব্যবহার করুন। সাম্প্রতিক সেল/পারচেজ লিস্ট থেকে বিস্তারিত দেখুন।',
            ],
        ],
        [
            'id' => 'products-menu',
            'icon' => 'fa-cube',
            'route' => 'products.index',
            'en' => [
                'title' => 'Products',
                'where' => 'Sidebar → Products',
                'what' => 'Product List, Create Product, Bulk Import, Categories, Brands, Print Barcode. Status Active/Inactive is shown on the list.',
                'how' => 'Create category/brand first → Add product (cost, sell price, unit, stock alert) → Optional: Bulk Import Excel → Print barcodes for shelf labels.',
            ],
            'bn' => [
                'title' => 'প্রোডাক্ট',
                'where' => 'সাইডবার → Products',
                'what' => 'প্রোডাক্ট লিস্ট, নতুন প্রোডাক্ট, Bulk Import, ক্যাটাগরি, ব্র্যান্ড, বারকোড প্রিন্ট। লিস্টে Active/Inactive স্ট্যাটাস দেখায়।',
                'how' => 'আগে ক্যাটাগরি/ব্র্যান্ড → প্রোডাক্ট যোগ (ক্রয়মূল্য, বিক্রয়মূল্য, ইউনিট) → চাইলে Excel Import → বারকোড প্রিন্ট।',
            ],
        ],
        [
            'id' => 'peoples-menu',
            'icon' => 'fa-users',
            'route' => 'customers.index',
            'en' => [
                'title' => 'Peoples (Customers & Suppliers)',
                'where' => 'Sidebar → Peoples',
                'what' => 'Customer Groups, Customers, Suppliers. Used in sales, purchases, and ledger reports.',
                'how' => 'Add suppliers before purchase. Add customers (and groups for discounts) before or during Quick Sale.',
            ],
            'bn' => [
                'title' => 'পিপলস (কাস্টমার ও সাপ্লায়ার)',
                'where' => 'সাইডবার → Peoples',
                'what' => 'কাস্টমার গ্রুপ, কাস্টমার, সাপ্লায়ার। সেল, পারচেজ ও লেজার রিপোর্টে ব্যবহার হয়।',
                'how' => 'ক্রয়ের আগে সাপ্লায়ার যোগ করুন। সেলের আগে/সময় কাস্টমার (ও ডিসকাউন্ট গ্রুপ) যোগ করুন।',
            ],
        ],
        [
            'id' => 'purchase-menu',
            'icon' => 'fa-bag-shopping',
            'route' => 'purchases.index',
            'en' => [
                'title' => 'Purchase',
                'where' => 'Sidebar → Purchase',
                'what' => 'Buy stock from suppliers. Status Received increases branch + global stock.',
                'how' => 'Add Purchase → select branch, supplier, products, qty & cost → set status Received → pay from an account. Later add payment from Purchase List if due.',
            ],
            'bn' => [
                'title' => 'পারচেজ (ক্রয়)',
                'where' => 'সাইডবার → Purchase',
                'what' => 'সাপ্লায়ার থেকে মাল কেনা। Status Received হলে ব্রাঞ্চ ও গ্লোবাল স্টক বাড়ে।',
                'how' => 'Add Purchase → ব্রাঞ্চ, সাপ্লায়ার, প্রোডাক্ট, পরিমাণ ও কস্ট → Received → অ্যাকাউন্ট থেকে পেমেন্ট। বাকি থাকলে পরে Purchase List থেকে পেমেন্ট যোগ করুন।',
            ],
        ],
        [
            'id' => 'inventory-menu',
            'icon' => 'fa-boxes-stacked',
            'route' => 'inventory.index',
            'en' => [
                'title' => 'Stock / Inventory',
                'where' => 'Sidebar → Stock',
                'what' => 'Live branch-wise stock and Stock History (purchase, sale, return, transfer, adjustment).',
                'how' => 'Open Inventory List to see qty per branch. Use Adjust Stock for corrections. Stock History shows every movement.',
            ],
            'bn' => [
                'title' => 'স্টক / ইনভেন্টরি',
                'where' => 'সাইডবার → Stock',
                'what' => 'ব্রাঞ্চভিত্তিক লাইভ স্টক এবং Stock History (ক্রয়, বিক্রি, রিটার্ন, ট্রান্সফার, অ্যাডজাস্ট)।',
                'how' => 'Inventory List-এ ব্রাঞ্চ অনুযায়ী qty দেখুন। ভুল হলে Adjust Stock। প্রতিটি মুভমেন্ট Stock History-তে লগ হয়।',
            ],
        ],
        [
            'id' => 'quick-sale-menu',
            'icon' => 'fa-cart-shopping',
            'route' => 'quick-sale.index',
            'en' => [
                'title' => 'Sale (POS)',
                'where' => 'Sidebar → Sale',
                'what' => 'Quick Sale (POS screen), Sale List, Gift Card, Coupons, Reward Points, Delivery.',
                'how' => 'Quick Sale: pick branch → scan/search product → customer → payment → Complete. Stock decreases. Print thermal/A4 invoice. Manage past invoices in Sale List.',
            ],
            'bn' => [
                'title' => 'সেল (POS)',
                'where' => 'সাইডবার → Sale',
                'what' => 'Quick Sale (POS স্ক্রিন), Sale List, Gift Card, Coupon, Reward Points, Delivery।',
                'how' => 'Quick Sale: ব্রাঞ্চ → প্রোডাক্ট স্ক্যান/সার্চ → কাস্টমার → পেমেন্ট → Complete। স্টক কমে। Thermal/A4 ইনভয়েস প্রিন্ট। পুরনো বিল Sale List-এ।',
            ],
        ],
        [
            'id' => 'return-menu',
            'icon' => 'fa-rotate-left',
            'route' => 'sale-return.index',
            'en' => [
                'title' => 'Returns',
                'where' => 'Sidebar → Return',
                'what' => 'Sale Return (customer) increases stock on Approve. Purchase Return (supplier) decreases stock on Approve.',
                'how' => 'Create return from original invoice → submit → Approve. Pending returns do not change stock until approved.',
            ],
            'bn' => [
                'title' => 'রিটার্ন',
                'where' => 'সাইডবার → Return',
                'what' => 'Sale Return (কাস্টমার) Approve হলে স্টক বাড়ে। Purchase Return (সাপ্লায়ার) Approve হলে স্টক কমে।',
                'how' => 'মূল বিল থেকে রিটার্ন তৈরি → সাবমিট → Approve। Pending থাকলে স্টক বদলায় না।',
            ],
        ],
        [
            'id' => 'transfers-menu',
            'icon' => 'fa-right-left',
            'route' => 'transfers.index',
            'en' => [
                'title' => 'Transfers',
                'where' => 'Sidebar → Transfers',
                'what' => 'Move stock between branches. Completed: source ↓ destination ↑ (global qty unchanged).',
                'how' => 'Add Transfer → from/to branch → products & qty → status Completed (or Sent then Complete). Check Inventory after transfer.',
            ],
            'bn' => [
                'title' => 'ট্রান্সফার',
                'where' => 'সাইডবার → Transfers',
                'what' => 'এক ব্রাঞ্চ থেকে অন্য ব্রাঞ্চে স্টক পাঠানো। Completed হলে উৎস ↓ গন্তব্য ↑ (গ্লোবাল qty একই থাকে)।',
                'how' => 'Add Transfer → from/to ব্রাঞ্চ → প্রোডাক্ট ও qty → Completed (বা Sent পরে Complete)। পরে Inventory চেক করুন।',
            ],
        ],
        [
            'id' => 'quotations-menu',
            'icon' => 'fa-file-invoice',
            'route' => 'quotations.index',
            'en' => [
                'title' => 'Quotations',
                'where' => 'Sidebar → Quotations',
                'what' => 'Price quotes for customers before sale/purchase. Can convert later.',
                'how' => 'Add Quotation → products & prices → share/print → later generate sale or purchase from it when confirmed.',
            ],
            'bn' => [
                'title' => 'কোটেশন',
                'where' => 'সাইডবার → Quotations',
                'what' => 'বিক্রি/ক্রয়ের আগে কাস্টমারকে দামের অফার। পরে কনভার্ট করা যায়।',
                'how' => 'Add Quotation → প্রোডাক্ট ও দাম → প্রিন্ট/শেয়ার → কনফার্ম হলে সেল বা পারচেজ জেনারেট করুন।',
            ],
        ],
        [
            'id' => 'accounts-menu',
            'icon' => 'fa-wallet',
            'route' => 'accounts.index',
            'en' => [
                'title' => 'Accounts & Expenses',
                'where' => 'Sidebar → Accounts',
                'what' => 'Cash/Bank accounts, Statement, Balance Sheet, Expenses & Expense Categories.',
                'how' => 'Create accounts first. Sales credit / purchases debit accounts. Record shop expenses under Expenses.',
            ],
            'bn' => [
                'title' => 'অ্যাকাউন্ট ও খরচ',
                'where' => 'সাইডবার → Accounts',
                'what' => 'ক্যাশ/ব্যাংক অ্যাকাউন্ট, Statement, Balance Sheet, Expenses ও ক্যাটাগরি।',
                'how' => 'আগে অ্যাকাউন্ট তৈরি করুন। সেলে ক্রেডিট / পারচেজে ডেবিট হয়। দোকানের খরচ Expenses-এ লিখুন।',
            ],
        ],
        [
            'id' => 'hrm-menu',
            'icon' => 'fa-users-gear',
            'route' => 'employees.index',
            'en' => [
                'title' => 'HRM',
                'where' => 'Sidebar → HRM',
                'what' => 'Employees (with login/role/branch), Attendance, Departments, Payrolls. Sellers appear in Quick Sale.',
                'how' => 'Add Department → Employee (optional user login + role) → Attendance → Payroll for salary payments.',
            ],
            'bn' => [
                'title' => 'এইচআরএম',
                'where' => 'সাইডবার → HRM',
                'what' => 'কর্মচারী (লগইন/ভূমিকা/ব্রাঞ্চ), উপস্থিতি, ডিপার্টমেন্ট, পে-রোল। Quick Sale-এ সেলার হিসেবে আসে।',
                'how' => 'ডিপার্টমেন্ট → এমপ্লয়ি (চাইলে ইউজার লগইন + ভূমিকা) → অ্যাটেনডেন্স → বেতনের জন্য Payroll।',
            ],
        ],
        [
            'id' => 'crm-menu',
            'icon' => 'fa-handshake',
            'route' => 'leads.index',
            'en' => [
                'title' => 'CRM (Leads)',
                'where' => 'Sidebar → CRM',
                'what' => 'Leads, Lead Source, Lead Status, Notes. Convert lead to customer when ready.',
                'how' => 'Set Source & Status → Add Lead → add notes → Convert to Customer when deal closes.',
            ],
            'bn' => [
                'title' => 'সিআরএম (লিড)',
                'where' => 'সাইডবার → CRM',
                'what' => 'লিড, সোর্স, স্ট্যাটাস, নোট। প্রয়োজনে লিডকে কাস্টমারে কনভার্ট।',
                'how' => 'Source ও Status সেট → Lead যোগ → নোট → ডিল হলে Customer-এ Convert।',
            ],
        ],
        [
            'id' => 'reports-menu',
            'icon' => 'fa-chart-line',
            'route' => 'reports.summary',
            'en' => [
                'title' => 'Reports',
                'where' => 'Sidebar → Reports',
                'what' => 'Summary (COGS profit), Sales, Purchases, Customer/Supplier Ledger, Inventory, Employees, Branch.',
                'how' => 'Pick date range & branch filters. Note: Summary profit uses COGS; Dashboard profit uses purchase totals — they can differ.',
            ],
            'bn' => [
                'title' => 'রিপোর্ট',
                'where' => 'সাইডবার → Reports',
                'what' => 'Summary (COGS লাভ), Sales, Purchases, Customer/Supplier Ledger, Inventory, Employees, Branch।',
                'how' => 'তারিখ ও ব্রাঞ্চ ফিল্টার দিন। মনে রাখুন: Summary লাভ COGS ভিত্তিক; Dashboard লাভ ক্রয়-মোট ভিত্তিক — আলাদা হতে পারে।',
            ],
        ],
        [
            'id' => 'settings-menu',
            'icon' => 'fa-gear',
            'route' => 'settings.general',
            'en' => [
                'title' => 'Settings',
                'where' => 'Sidebar → Settings',
                'what' => 'Company logo/favicon, timezone (searchable), default payment, colors, print layout (A4/thermal), terms, mail, branches (BIN/phone/web), roles/permissions, users, units, currencies, activity logs.',
                'how' => 'Set General Settings (timezone Europe/Madrid) & Colors first. Print Settings to reorder invoice blocks. Create Roles, then Users. Branches max 5.',
            ],
            'bn' => [
                'title' => 'সেটিংস',
                'where' => 'সাইডবার → Settings',
                'what' => 'কোম্পানি লোগো/ফেভিকন, টাইমজোন (সার্চযোগ্য), ডিফল্ট পেমেন্ট, কালার, প্রিন্ট লেআউট (A4/থার্মাল), টার্মস, মেইল, ব্রাঞ্চ (BIN/ফোন/ওয়েব), ভূমিকা/পারমিশন, ইউজার, ইউনিট, কারেন্সি, অ্যাক্টিভিটি লগ।',
                'how' => 'আগে General (Europe/Madrid) ও Color Settings। Print Settings-এ ইনভয়েস ব্লক সাজান। ভূমিকা + ইউজার। ব্রাঞ্চ সর্বোচ্চ ৫টি।',
            ],
        ],
        [
            'id' => 'sms-menu',
            'icon' => 'fa-comment-sms',
            'route' => 'sms_logs.index',
            'en' => [
                'title' => 'SMS',
                'where' => 'Sidebar → SMS / API Settings',
                'what' => 'SMS templates & logs. Configure gateway under API Settings → SMS Settings.',
                'how' => 'Set API credentials → create templates → system sends SMS; check SMS Logs for success/fail.',
            ],
            'bn' => [
                'title' => 'এসএমএস',
                'where' => 'সাইডবার → SMS / API Settings',
                'what' => 'এসএমএস টেমপ্লেট ও লগ। গেটওয়ে API Settings → SMS Settings-এ।',
                'how' => 'API সেটআপ → টেমপ্লেট তৈরি → সিস্টেম SMS পাঠায়; সফল/ব্যর্থ SMS Logs-এ দেখুন।',
            ],
        ],
        [
            'id' => 'api-settings-menu',
            'icon' => 'fa-code',
            'route' => 'sms_settings.index',
            'en' => [
                'title' => 'API Settings',
                'where' => 'Sidebar → API Settings',
                'what' => 'SMS gateway, Courier (Pathao/Steadfast etc.), Fraud Check API.',
                'how' => 'Enter API keys → test → use Delivery module for courier orders and fraud checks on checkout if enabled.',
            ],
            'bn' => [
                'title' => 'এপিআই সেটিংস',
                'where' => 'সাইডবার → API Settings',
                'what' => 'SMS গেটওয়ে, কুরিয়ার (Pathao/Steadfast ইত্যাদি), Fraud Check API।',
                'how' => 'API কী দিন → টেস্ট → Delivery মডিউলে কুরিয়ার অর্ডার ও প্রয়োজনে fraud check ব্যবহার করুন।',
            ],
        ],
    ],

    'faqs' => [
        [
            'category' => ['en' => 'Getting Started', 'bn' => 'শুরু করার আগে'],
            'items' => [
                [
                    'en' => [
                        'q' => 'I am new. What should I set up first?',
                        'a' => 'Follow this order: Settings (branch, unit, currency, roles/users) → Products (category, brand, products) → Peoples (supplier/customer) → Accounts (cash/bank) → Purchase (Received) → Quick Sale → Reports.',
                    ],
                    'bn' => [
                        'q' => 'নতুন ইউজার — আগে কী সেটআপ করব?',
                        'a' => 'এই অর্ডার অনুসরণ করুন: Settings (ব্রাঞ্চ, ইউনিট, কারেন্সি, ভূমিকা/ইউজার) → Products → Peoples (সাপ্লায়ার/কাস্টমার) → Accounts → Purchase (Received) → Quick Sale → Reports।',
                    ],
                ],
                [
                    'en' => [
                        'q' => 'Where do I change company logo and theme color?',
                        'a' => 'Settings → General Settings (logo, favicon, company info) and Settings → Color Settings (primary theme colors).',
                    ],
                    'bn' => [
                        'q' => 'কোম্পানি লোগো ও থিম কালার কোথায় বদলাব?',
                        'a' => 'Settings → General Settings (লোগো, ফেভিকন, তথ্য) এবং Settings → Color Settings (প্রাইমারি কালার)।',
                    ],
                ],
            ],
        ],
        [
            'category' => ['en' => 'Sales & Stock', 'bn' => 'সেল ও স্টক'],
            'items' => [
                [
                    'en' => [
                        'q' => 'Why is Quick Sale showing no stock / insufficient stock?',
                        'a' => 'Stock comes from Purchase with status Received (or Import / Adjust). Check Stock → Inventory for that branch. Transfer may have moved stock to another branch.',
                    ],
                    'bn' => [
                        'q' => 'Quick Sale-এ স্টক নেই / Insufficient stock কেন?',
                        'a' => 'স্টক আসে Purchase Received (বা Import / Adjust) থেকে। Stock → Inventory-তে সেই ব্রাঞ্চ চেক করুন। অন্য ব্রাঞ্চে ট্রান্সফার হয়ে থাকতে পারে।',
                    ],
                ],
                [
                    'en' => [
                        'q' => 'Does sale return increase stock?',
                        'a' => 'Yes — only after you Approve the Sale Return. Pending returns do not change stock.',
                    ],
                    'bn' => [
                        'q' => 'সেল রিটার্ন করলে স্টক বাড়ে?',
                        'a' => 'হ্যাঁ — শুধু Approve করার পর। Pending থাকলে স্টক বদলায় না।',
                    ],
                ],
                [
                    'en' => [
                        'q' => 'What happens on product transfer?',
                        'a' => 'On Completed transfer: source branch stock decreases, destination increases. Total (global) quantity stays the same.',
                    ],
                    'bn' => [
                        'q' => 'প্রোডাক্ট ট্রান্সফার হলে কী হয়?',
                        'a' => 'Completed হলে উৎস ব্রাঞ্চের স্টক কমে, গন্তব্যে বাড়ে। মোট (গ্লোবাল) পরিমাণ একই থাকে।',
                    ],
                ],
                [
                    'en' => [
                        'q' => 'Where do I print invoices?',
                        'a' => 'After Quick Sale you can print Thermal or A4. From Sale List open an invoice and use the print options. Thermal size is in General Settings. Layout blocks are in Print Settings.',
                    ],
                    'bn' => [
                        'q' => 'ইনভয়েস কোথায় প্রিন্ট করব?',
                        'a' => 'Quick Sale শেষে Thermal বা A4 প্রিন্ট করা যায়। Sale List থেকে বিল খুলেও প্রিন্ট করতে পারবেন। Thermal সাইজ General Settings-এ। লেআউট Print Settings-এ।',
                    ],
                ],
                [
                    'en' => [
                        'q' => 'Does the invoice show cash given and change?',
                        'a' => 'Yes. If the customer pays more than the total, the invoice shows Customer Paid and Change / Return. Due is shown when they pay less.',
                    ],
                    'bn' => [
                        'q' => 'বিলে গ্রাহক কত দিল ও ফেরত কত দেখায়?',
                        'a' => 'হ্যাঁ। মোটের বেশি দিলে Customer Paid ও Change / Return দেখায়। কম দিলে Due দেখায়।',
                    ],
                ],
            ],
        ],
        [
            'category' => ['en' => 'Purchase & Money', 'bn' => 'ক্রয় ও টাকা'],
            'items' => [
                [
                    'en' => [
                        'q' => 'Purchase done but stock did not increase?',
                        'a' => 'Stock increases only when purchase status is Received. Ordered/Pending does not add stock.',
                    ],
                    'bn' => [
                        'q' => 'পারচেজ করেছি কিন্তু স্টক বাড়েনি?',
                        'a' => 'শুধু Status Received হলে স্টক বাড়ে। Ordered/Pending স্টক বাড়ায় না।',
                    ],
                ],
                [
                    'en' => [
                        'q' => 'Why Dashboard profit and Summary Report profit are different?',
                        'a' => 'Dashboard uses cash/purchase formula: (Sales−SaleReturn)−(Purchases−PurchaseReturn)−Expense. Summary uses COGS (cost of goods sold). Buying a lot of stock makes Dashboard look like a loss even if sales are profitable.',
                    ],
                    'bn' => [
                        'q' => 'Dashboard লাভ আর Summary Report লাভ আলাদা কেন?',
                        'a' => 'Dashboard ক্রয়ের পুরো টাকা খরচ ধরে। Summary শুধু বিক্রি হওয়া মালের ক্রয়মূল্য (COGS) ধরে। বেশি স্টক কিনলে Dashboard লস দেখাতে পারে, যদিও বিক্রির লাভ আছে।',
                    ],
                ],
                [
                    'en' => [
                        'q' => 'Where do I record shop expense (rent, bill)?',
                        'a' => 'Accounts → View Expenses (create expense category first if needed). It affects dashboard/report profit.',
                    ],
                    'bn' => [
                        'q' => 'ভাড়া/বিল খরচ কোথায় লিখব?',
                        'a' => 'Accounts → View Expenses (প্রয়োজনে আগে Expenses Categories)। এটি ড্যাশবোর্ড/রিপোর্টের লাভে কাটে।',
                    ],
                ],
            ],
        ],
        [
            'category' => ['en' => 'Users & Permissions', 'bn' => 'ইউজার ও পারমিশন'],
            'items' => [
                [
                    'en' => [
                        'q' => 'Employee cannot see Quick Sale menu?',
                        'a' => 'Give the role permission view_quick_sale (and create_quick_sale to sell). Settings → Manage Roles → permissions. Clear cache after changes.',
                    ],
                    'bn' => [
                        'q' => 'এমপ্লয়ি Quick Sale মেনু দেখতে পাচ্ছে না?',
                        'a' => 'ভূমিকায় view_quick_sale (বিক্রির জন্য create_quick_sale) দিন। Settings → Manage Roles। পরিবর্তনের পর Cache Clear করুন।',
                    ],
                ],
                [
                    'en' => [
                        'q' => 'How do I create a staff login?',
                        'a' => 'HRM → Employee: set branch, role, email & password (creates linked user). Or Settings → Manage Users.',
                    ],
                    'bn' => [
                        'q' => 'স্টাফ লগইন কীভাবে তৈরি করব?',
                        'a' => 'HRM → Employee-এ ব্রাঞ্চ, ভূমিকা, ইমেইল ও পাসওয়ার্ড দিন (ইউজার তৈরি হয়)। অথবা Settings → Manage Users।',
                    ],
                ],
            ],
        ],
        [
            'category' => ['en' => 'Reports & Help', 'bn' => 'রিপোর্ট ও হেল্প'],
            'items' => [
                [
                    'en' => [
                        'q' => 'Which report shows real business profit?',
                        'a' => 'Reports → Summary Reports (COGS-based). Use Inventory Report for stock value. Customer/Supplier Ledger for dues.',
                    ],
                    'bn' => [
                        'q' => 'আসল ব্যবসার লাভ কোন রিপোর্টে?',
                        'a' => 'Reports → Summary Reports (COGS ভিত্তিক)। স্টক মূল্য Inventory Report-এ। বাকি দেখতে Customer/Supplier Ledger।',
                    ],
                ],
                [
                    'en' => [
                        'q' => 'How do I restart the UI tour?',
                        'a' => 'Click “Start Tour” in the top bar, or open Help & FAQ and press Start UI Tour. Language can be switched EN/BN on the Help page.',
                    ],
                    'bn' => [
                        'q' => 'UI ট্যুর আবার কীভাবে চালাব?',
                        'a' => 'টপবারে “Start Tour” চাপুন, অথবা Help & FAQ পেজে গিয়ে Start UI Tour চাপুন। ভাষা EN/BN সুইচ করা যায়।',
                    ],
                ],
                [
                    'en' => [
                        'q' => 'Can I use this POS on a phone?',
                        'a' => 'Yes — the app is built for mobile. Open the menu with the grid icon, use Quick Sale to scan/search products, tap View Cart at the bottom to pay. Tables scroll sideways. Use Help tour on the phone too.',
                    ],
                    'bn' => [
                        'q' => 'মোবাইলে POS ব্যবহার করা যাবে?',
                        'a' => 'হ্যাঁ — মোবাইলের জন্য তৈরি। গ্রিড আইকনে মেনু, Quick Sale-এ স্ক্যান/সার্চ, নিচে View Cart দিয়ে পে। টেবিল পাশে স্ক্রল হয়। ট্যুরও ফোনে চালানো যায়।',
                    ],
                ],
                [
                    'en' => [
                        'q' => 'How do I set Spain timezone?',
                        'a' => 'Settings → General Settings → Timezone. Type to search (Madrid / Canary). The header clock follows this timezone, not the phone clock. Save after selecting.',
                    ],
                    'bn' => [
                        'q' => 'স্পেনের টাইমজোন কীভাবে সেট করব?',
                        'a' => 'Settings → General Settings → Timezone। Madrid বা Canary সার্চ করুন। হেডারের ঘড়ি এই টাইমজোন দেখায়, ফোনের ঘড়ি নয়। সিলেক্ট করে Save করুন।',
                    ],
                ],
                [
                    'en' => [
                        'q' => 'Where do I change invoice layout (A4 / thermal)?',
                        'a' => 'Settings → Print Settings. Drag blocks to reorder, toggle visibility, preview A4 and thermal. Branch BIN, phone and website print from Branches.',
                    ],
                    'bn' => [
                        'q' => 'ইনভয়েস লেআউট (A4 / থার্মাল) কোথায় বদলাব?',
                        'a' => 'Settings → Print Settings। ব্লক ড্র্যাগ করে সাজান, দেখানো/লুকানো টগল করুন। ব্রাঞ্চের BIN, ফোন, ওয়েবসাইট Branches থেকে প্রিন্টে যায়।',
                    ],
                ],
            ],
        ],
    ],

    'tour' => [
        'en' => [
            'welcome' => ['title' => 'Welcome to ONEPOS', 'desc' => 'This short tour shows where each module lives and what it does. You can reopen it anytime from Help or Start Tour.'],
            'sidebar' => ['title' => 'Left Sidebar Menu', 'desc' => 'All modules are here. Use Search menu to find anything quickly.'],
            'dashboard' => ['title' => 'Dashboard', 'desc' => 'Daily business snapshot: sales, purchases, profit, dues. Use date filters at the top of the page.'],
            'products' => ['title' => 'Products', 'desc' => 'Create products, categories, brands, bulk import, and print barcodes.'],
            'purchase' => ['title' => 'Purchase', 'desc' => 'Buy stock from suppliers. Use status Received to increase inventory.'],
            'sale' => ['title' => 'Sale / POS', 'desc' => 'Quick Sale is your billing screen. Sale List holds past invoices, coupons, gift cards, delivery.'],
            'stock' => ['title' => 'Stock', 'desc' => 'Check live inventory per branch and full stock movement history.'],
            'returns' => ['title' => 'Returns', 'desc' => 'Sale return increases stock on approve. Purchase return decreases stock on approve.'],
            'transfers' => ['title' => 'Transfers', 'desc' => 'Move products between branches without changing total stock.'],
            'accounts' => ['title' => 'Accounts', 'desc' => 'Cash/bank balances, statements, expenses, and balance sheet.'],
            'reports' => ['title' => 'Reports', 'desc' => 'Summary, sales, purchase, ledgers, inventory, employee & branch reports.'],
            'settings' => ['title' => 'Settings', 'desc' => 'Company info, colors, branches, roles, users, units, currencies.'],
            'peoples' => ['title' => 'Peoples', 'desc' => 'Customers, customer groups, and suppliers. Add these before sales and purchases.'],
            'quotations' => ['title' => 'Quotations', 'desc' => 'Send price quotes, then convert to sale or purchase when confirmed.'],
            'hrm' => ['title' => 'HRM', 'desc' => 'Employees, attendance, departments, payroll. Staff can log in with a role and appear as sellers in POS.'],
            'crm' => ['title' => 'CRM', 'desc' => 'Leads, sources, status, notes. Convert a lead to a customer when the deal closes.'],
            'sms' => ['title' => 'SMS', 'desc' => 'Templates and send logs. Gateway keys live under API Settings.'],
            'api' => ['title' => 'API Settings', 'desc' => 'SMS gateway, courier (Pathao/Steadfast), and fraud-check APIs.'],
            'clock' => ['title' => 'Live clock', 'desc' => 'Shows your shop timezone from General Settings — not the phone clock. Search timezone there (e.g. Madrid).'],
            'posSearch' => ['title' => 'Scan or search', 'desc' => 'Type name/barcode or tap the QR icon to scan. Works well on a phone camera.'],
            'posCart' => ['title' => 'Cart & Pay', 'desc' => 'On phone, tap View Cart at the bottom. Add customer, tax, then Pay to complete.'],
            'posMobileCart' => ['title' => 'Open cart', 'desc' => 'This floating button opens the cart on mobile. Complete payment from there.'],
            'help' => ['title' => 'Help Center', 'desc' => 'Open Help Center anytime for module guides and FAQ in English & Bangla. On a phone, tap the question-mark icon in the header.'],
            'done' => ['title' => 'You are ready!', 'desc' => 'Tip: set up Settings → Products → Purchase → Quick Sale. Open Help & FAQ for more detail.'],
        ],
        'bn' => [
            'welcome' => ['title' => 'ONEPOS-এ স্বাগতম', 'desc' => 'এই ছোট ট্যুরে দেখাবে কোন মডিউল কোথায় এবং কী করে। Help বা Start Tour থেকে আবার চালাতে পারবেন।'],
            'sidebar' => ['title' => 'বাম সাইডবার মেনু', 'desc' => 'সব মডিউল এখানে। Search menu দিয়ে দ্রুত খুঁজুন।'],
            'dashboard' => ['title' => 'ড্যাশবোর্ড', 'desc' => 'দৈনিক ব্যবসার সারাংশ: বিক্রি, ক্রয়, লাভ, বাকি। পেজের ডেট ফিল্টার ব্যবহার করুন।'],
            'products' => ['title' => 'প্রোডাক্ট', 'desc' => 'প্রোডাক্ট, ক্যাটাগরি, ব্র্যান্ড, বাল্ক ইমপোর্ট ও বারকোড প্রিন্ট।'],
            'purchase' => ['title' => 'পারচেজ', 'desc' => 'সাপ্লায়ার থেকে ক্রয়। Received স্ট্যাটাসে স্টক বাড়ে।'],
            'sale' => ['title' => 'সেল / POS', 'desc' => 'Quick Sale হলো বিলিং স্ক্রিন। Sale List-এ পুরনো বিল, কুপন, গিফট কার্ড, ডেলিভারি।'],
            'stock' => ['title' => 'স্টক', 'desc' => 'ব্রাঞ্চভিত্তিক লাইভ ইনভেন্টরি ও স্টক হিস্টরি।'],
            'returns' => ['title' => 'রিটার্ন', 'desc' => 'সেল রিটার্ন Approve-এ স্টক ↑। পারচেজ রিটার্ন Approve-এ স্টক ↓।'],
            'transfers' => ['title' => 'ট্রান্সফার', 'desc' => 'ব্রাঞ্চের মধ্যে মাল সরান; মোট স্টক একই থাকে।'],
            'accounts' => ['title' => 'অ্যাকাউন্ট', 'desc' => 'ক্যাশ/ব্যাংক ব্যালেন্স, স্টেটমেন্ট, খরচ, ব্যালেন্স শিট।'],
            'reports' => ['title' => 'রিপোর্ট', 'desc' => 'সারাংশ, সেল, পারচেজ, লেজার, ইনভেন্টরি, এমপ্লয়ি ও ব্রাঞ্চ রিপোর্ট।'],
            'settings' => ['title' => 'সেটিংস', 'desc' => 'কোম্পানি তথ্য, কালার, ব্রাঞ্চ, ভূমিকা, ইউজার, ইউনিট, কারেন্সি।'],
            'peoples' => ['title' => 'পিপলস', 'desc' => 'কাস্টমার, গ্রুপ ও সাপ্লায়ার। সেল/পারচেজের আগে যোগ করুন।'],
            'quotations' => ['title' => 'কোটেশন', 'desc' => 'দামের অফার পাঠান; কনফার্ম হলে সেল বা পারচেজ করুন।'],
            'hrm' => ['title' => 'এইচআরএম', 'desc' => 'কর্মচারী, উপস্থিতি, ডিপার্টমেন্ট, পে-রোল। ভূমিকা দিয়ে লগইন ও POS-এ সেলার।'],
            'crm' => ['title' => 'সিআরএম', 'desc' => 'লিড, সোর্স, স্ট্যাটাস, নোট। ডিল হলে কাস্টমারে কনভার্ট।'],
            'sms' => ['title' => 'এসএমএস', 'desc' => 'টেমপ্লেট ও লগ। গেটওয়ে API Settings-এ।'],
            'api' => ['title' => 'এপিআই সেটিংস', 'desc' => 'SMS, কুরিয়ার (Pathao/Steadfast) ও fraud-check API।'],
            'clock' => ['title' => 'লাইভ ঘড়ি', 'desc' => 'General Settings-এর টাইমজোন দেখায়, ফোনের ঘড়ি নয়। Madrid সার্চ করে সেট করুন।'],
            'posSearch' => ['title' => 'স্ক্যান বা সার্চ', 'desc' => 'নাম/বারকোড টাইপ করুন বা QR আইকনে ক্যামেরা স্ক্যান। মোবাইলে ভালো কাজ করে।'],
            'posCart' => ['title' => 'কার্ট ও পে', 'desc' => 'ফোনে নিচে View Cart চাপুন। কাস্টমার, ট্যাক্স, তারপর Pay।'],
            'posMobileCart' => ['title' => 'কার্ট খুলুন', 'desc' => 'মোবাইলে এই বাটনে কার্ট খোলে। এখান থেকে পেমেন্ট শেষ করুন।'],
            'help' => ['title' => 'Help Center', 'desc' => 'মডিউল গাইড ও FAQ ইংরেজি/বাংলায় Help Center থেকে খুলুন। হেডারে ? আইকনও আছে।'],
            'done' => ['title' => 'প্রস্তুত!', 'desc' => 'টিপস: Settings → Products → Purchase → Quick Sale। বিস্তারিত জানতে Help & FAQ খুলুন।'],
        ],
    ],
];
