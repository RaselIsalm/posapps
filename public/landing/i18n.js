/**
 * OnePOS i18n
 *
 * Data-attribute conventions (wire these in HTML later):
 *   data-i18n="key.path"           → sets textContent from dictionary
 *   data-i18n-html="key.path"      → sets innerHTML from dictionary
 *   data-i18n-attr="attr:key"      → sets one attribute (e.g. aria-label:nav.openMenu)
 *   data-i18n-attr="a:k1,b:k2"     → multiple comma-separated attr:key pairs
 *                                    also used for data-package, data-billing,
 *                                    data-monthly, data-wa-group on package cards
 *   button[data-lang="en|bn"]      → language switcher (.lang-switch); applyLang
 *                                    toggles .is-active + aria-pressed
 *
 * WhatsApp: store plain message text under wa.* keys. When applying to href,
 * if the key starts with "wa." the value is wrapped as
 *   https://wa.me/8801994433400?text= + encodeURIComponent(message)
 * Use subject:wa.mailSubject for mailto (not an href wa.me build).
 *
 * Prices/currency amounts stay in HTML; translate labels only.
 * Price-meta lines that mix numbers + words are full translated strings.
 */
(function () {
  "use strict";

  var STORAGE_KEY = "onepos_lang";
  var DEFAULT_LANG = "en";
  var WA_NUMBER = "8801994433400";
  var SUPPORTED = { en: true, bn: true };

  var STRINGS = {
    en: {
      meta: {
        title: "OnePOS by ONETECH | Smart POS for Any Business",
        description:
          "OnePOS by ONETECH - a flexible custom POS solution built for any business.",
      },
      brand: {
        home: "OnePOS home",
      },
      nav: {
        features: "Features",
        payments: "Payments",
        businesses: "Businesses",
        packages: "Packages",
        contact: "Contact",
        whatsapp: "WhatsApp Us",
        openMenu: "Open menu",
        primary: "Primary navigation",
      },
      hero: {
        eyebrow: "Custom POS solution by ONETECH",
        title: "ONEPOS",
        titleSpan: "Built around your business.",
        text: "A flexible point-of-sale system for retail, restaurants, pharmacies, warehouses and growing chains — configured the way you actually work.",
        ctaPackages: "View Packages",
        ctaDemo: "Request a Demo",
        point1: "Customizable modules",
        point2: "Multi-business ready",
        point3: "Local ONETECH support",
        previewLabel: "OnePOS interface preview",
      },
      mock: {
        dashboardTitle: "OnePOS Dashboard",
        welcome: "Welcome back",
        storeOverview: "Store overview",
        today: "Today",
        thisMonth: "This Month",
        totalSale: "Total Sale",
        totalPurchase: "Total Purchase",
        totalDue: "Total Due",
        salesTrend: "Sales trend",
        sevenDays: "7 days",
        cashPosition: "Cash position",
        paid: "Paid",
        due: "Due",
        balance: "Balance",
        todaysSales: "Today's Sales",
        inventory: "Inventory",
        stockSynced: "Stock synced",
      },
      trust: {
        flexible: "Flexible",
        flexibleDesc: "Fits your workflow",
        scalable: "Scalable",
        scalableDesc: "Single shop to multi-branch",
        complete: "Complete",
        completeDesc: "Sale to reports in one place",
        supported: "Supported",
        supportedDesc: "ONETECH local team",
      },
      feat: {
        eyebrow: "Core modules",
        title: "What each section does",
        navLabel: "Module categories",
        groupOps: "Operations",
        groupCatalog: "Catalog & people",
        groupFinance: "Finance",
        groupGrowth: "Growth & team",
        groupSystem: "System",
        badgeOps: "Operations",
        badgeCatalog: "Catalog & people",
        badgeFinance: "Finance",
        badgeGrowth: "Growth & team",
        badgeSystem: "System",

        navDashboard: "Dashboard",
        navSale: "Sale",
        navPurchase: "Purchase",
        navStock: "Stock",
        navReturn: "Return",
        navTransfers: "Transfers",
        navProducts: "Products",
        navPeoples: "Peoples",
        navQuotations: "Quotations",
        navAccounts: "Accounts",
        navCrm: "CRM",
        navHrm: "HRM",
        navSms: "SMS",
        navSettings: "Settings",
        navApi: "API Settings",
        navReports: "Reports",

        dashboardTitle: "Dashboard",
        dashboardDesc:
          "Your store overview — live numbers, shortcuts and help when you need it.",
        dashboardCard1: "Dashboard",
        dashboardCard1Desc:
          "See sales, purchases, dues, profit and cash position at a glance.",
        dashboardCard2: "Help & FAQ",
        dashboardCard2Desc:
          "Quick answers and guidance for common OnePOS tasks.",

        saleTitle: "Sale",
        saleDesc:
          "Run the counter fast — bill customers, manage offers and handle delivery from one place.",
        saleQuick: "Quick Sale",
        saleQuickDesc:
          "Speed checkout for busy counters with barcode and hold support.",
        saleList: "Sale List",
        saleListDesc: "Search, view and manage every past sales invoice.",
        saleGift: "Gift Card",
        saleGiftDesc: "Issue, track and redeem store gift cards.",
        saleCoupons: "Coupons",
        saleCouponsDesc: "Create and control discount codes for promotions.",
        saleReward: "Reward Points",
        saleRewardDesc:
          "Run loyalty points so repeat customers earn and redeem.",
        saleDelivery: "Delivery",
        saleDeliveryDesc: "Manage delivery orders and handoff status.",

        purchaseTitle: "Purchase",
        purchaseDesc:
          "Record supplier buys and keep purchase history tidy for costing and stock.",
        purchaseList: "Purchase List",
        purchaseListDesc: "Browse and filter all supplier purchases and costs.",
        purchaseAdd: "Add Purchase",
        purchaseAddDesc:
          "Enter a new purchase with items, qty and supplier details.",

        stockTitle: "Stock",
        stockDesc: "Know what you have on hand and how stock moved over time.",
        stockInventory: "Inventory List",
        stockInventoryDesc:
          "Live quantities, availability and stock value by product.",
        stockHistory: "Stock History",
        stockHistoryDesc:
          "Track additions, deductions and adjustments over time.",

        returnTitle: "Return",
        returnDesc:
          "Handle product returns cleanly for both customers and suppliers.",
        returnSale: "Sale Return",
        returnSaleDesc: "Process items customers bring back and update stock.",
        returnPurchase: "Purchase Return",
        returnPurchaseDesc:
          "Return goods to suppliers and adjust purchase records.",

        transfersTitle: "Transfers",
        transfersDesc:
          "Move stock between branches or warehouses without losing track.",
        transfersList: "Transfers",
        transfersListDesc: "View pending and completed stock movements.",
        transfersAdd: "Add Transfer",
        transfersAddDesc: "Create a new transfer and update inventory live.",

        productsTitle: "Products",
        productsDesc:
          "Build and organise your catalogue — items, categories, brands and barcodes.",
        productsList: "Product List",
        productsListDesc: "Search, filter and manage every product in one place.",
        productsCreate: "Create Product",
        productsCreateDesc: "Add a new item with price, tax, SKU and stock.",
        productsBulk: "Bulk Product Import",
        productsBulkDesc: "Upload many products at once via spreadsheet.",
        productsCategories: "Categories",
        productsCategoriesDesc: "Group products for faster billing and reports.",
        productsBrands: "Brands",
        productsBrandsDesc: "Manage manufacturer or brand names on items.",
        productsBarcode: "Print Barcode",
        productsBarcodeDesc: "Generate and print scannable product labels.",

        peoplesTitle: "Peoples",
        peoplesDesc:
          "Keep customers and suppliers organised with groups and full profiles.",
        peoplesGroups: "Customer Groups",
        peoplesGroupsDesc: "Segment clients (retail, wholesale and more).",
        peoplesCustomers: "Customers",
        peoplesCustomersDesc: "Profiles, history and contact details for buyers.",
        peoplesSuppliers: "Suppliers",
        peoplesSuppliersDesc: "Vendor records for purchasing and dues.",

        quotationsTitle: "Quotations",
        quotationsDesc:
          "Send price estimates to customers, then turn accepted quotes into sales.",
        quotationsAdd: "Add Quotations",
        quotationsAddDesc:
          "Build and send a new quote with products and pricing.",
        quotationsList: "Quotations List",
        quotationsListDesc: "Track quote status, follow up and convert to sale.",

        accountsTitle: "Accounts",
        accountsDesc:
          "Follow money in and out — accounts, statements, expenses and balances.",
        accountsView: "View Accounts",
        accountsViewDesc: "Manage cash, bank and other financial accounts.",
        accountsStatement: "Statement",
        accountsStatementDesc:
          "Generate account statements for any date range.",
        accountsBalance: "Balance Sheet",
        accountsBalanceDesc:
          "See assets, liabilities and overall financial position.",
        accountsExpenses: "View Expenses",
        accountsExpensesDesc: "Track every business expense in one list.",
        accountsExpenseCat: "Expenses Categories",
        accountsExpenseCatDesc:
          "Organize spending by rent, utilities, stock and more.",

        crmTitle: "CRM",
        crmDesc: "Capture leads and follow them through to customers.",
        crmLeads: "Leads",
        crmLeadsDesc: "Track potential customers in your pipeline.",
        crmSource: "Lead Source",
        crmSourceDesc: "See where leads come from — ads, referral, walk-in.",
        crmStatus: "Lead Status",
        crmStatusDesc: "Move leads through stages like new, contacted, won.",
        crmNotes: "Lead Notes",
        crmNotesDesc: "Save follow-up notes against each lead.",

        hrmTitle: "HRM",
        hrmDesc: "Manage staff, attendance, departments and payroll basics.",
        hrmEmployee: "Employee",
        hrmEmployeeDesc: "Staff profiles and employment records.",
        hrmAttendance: "Attendance",
        hrmAttendanceDesc: "Track who came in and when.",
        hrmDepartment: "Department",
        hrmDepartmentDesc: "Organise teams by department.",
        hrmPayrolls: "Payrolls",
        hrmPayrollsDesc: "Salary processing and payroll records.",

        smsTitle: "SMS",
        smsDesc:
          "Message customers and staff with reusable templates and a full send history.",
        smsLogs: "SMS Logs",
        smsLogsDesc: "History of sent messages and delivery status.",
        smsTemplates: "SMS Templates",
        smsTemplatesDesc:
          "Save reusable texts for invoices, reminders and offers.",

        settingsTitle: "Settings",
        settingsDesc:
          "Configure the store, branding, print, users, branches and system security.",
        settingsGeneral: "General Settings",
        settingsGeneralDesc:
          "Shop name, contacts, address, logo and timezone.",
        settingsColor: "Color Settings",
        settingsColorDesc: "Match the app look to your brand colours.",
        settingsPrint: "Print Settings",
        settingsPrintDesc: "Invoice/receipt templates and printer setup.",
        settingsTerms: "Terms & Conditions",
        settingsTermsDesc: "Text that appears on customer documents.",
        settingsMail: "Mail Settings",
        settingsMailDesc: "SMTP setup for invoices, reports and alerts.",
        settingsBranches: "Branches",
        settingsBranchesDesc: "Manage multiple outlets from one system.",
        settingsRoles: "Manage Roles",
        settingsRolesDesc:
          "Define Admin, Cashier, Manager and other permissions.",
        settingsUsers: "Manage Users",
        settingsUsersDesc: "Add staff accounts and assign roles/branches.",
        settingsUnits: "Units",
        settingsUnitsDesc: "Set product units like kg, pcs and liter.",
        settingsCurrencies: "Currencies",
        settingsCurrenciesDesc: "Primary currency and exchange options.",
        settingsLogs: "Activities Logs",
        settingsLogsDesc: "Audit trail of user actions for security.",

        apiTitle: "API Settings",
        apiDesc: "Connect OnePOS to SMS gateways, couriers and fraud checks.",
        apiSms: "SMS Settings",
        apiSmsDesc: "Configure SMS gateway for alerts and confirmations.",
        apiCourier: "Courier Settings",
        apiCourierDesc: "Link courier partners for shipping and tracking.",
        apiFraud: "Fraud Check API",
        apiFraudDesc: "Flag risky orders with fraud detection tools.",

        reportsTitle: "Reports",
        reportsDesc:
          "Understand performance across sales, stock, people and branches.",
        reportsSummary: "Summary Reports",
        reportsSummaryDesc: "High-level business performance in one view.",
        reportsSales: "Sales Report",
        reportsSalesDesc: "Revenue, invoices and product movement.",
        reportsPurchases: "Purchases Report",
        reportsPurchasesDesc: "Supplier buys and purchase spend.",
        reportsCustomerLedger: "Customer Ledger Report",
        reportsCustomerLedgerDesc:
          "Per-customer balances, payments and dues.",
        reportsSupplierLedger: "Supplier Ledger Report",
        reportsSupplierLedgerDesc:
          "Per-supplier balances and payment history.",
        reportsInventory: "Inventory Report",
        reportsInventoryDesc: "Stock levels, value and movement.",
        reportsEmployees: "Employees Report",
        reportsEmployeesDesc: "Staff performance and related HR figures.",
        reportsBranch: "Branch Report",
        reportsBranchDesc: "Compare results across outlets.",
      },
      pay: {
        eyebrow: "Payment system",
        title: "Every payment. Crystal clear.",
        intro:
          "Built for Bangladesh retail — take payment the way your customers pay, then see paid, due and balances in one place.",
        h1: "Partial payments and remaining due",
        h2: "Customer due receive & purchase due",
        h3: "Paid vs unpaid visibility on every bill",
        h4: "Account balance stay in sync with cash flow",
        methodsLabel: "Supported payment methods",
        cash: "Cash",
        card: "Card",
        mobileBanking: "Mobile banking",
        bankTransfer: "Bank transfer",
        dueCredit: "Due / credit",
        totalPaid: "Total Paid",
        totalDue: "Total Due",
        purchaseDue: "Purchase Due",
        accountBalance: "Account Balance",
        note: "Cash, card, bKash, Nagad and other mobile wallets — configured for your store.",
      },
      biz: {
        eyebrow: "Made for real businesses",
        title: "OnePOS can adapt to almost any business model",
        text: "Instead of forcing your business into a fixed software flow, OnePOS can be configured around your products, staff, branches, reporting and operational needs.",
        link: "Discuss your business setup →",
        retail: "Retail Shop",
        restaurant: "Restaurant",
        pharmacy: "Pharmacy",
        superShop: "Super Shop",
        wholesale: "Wholesale",
        fashion: "Fashion",
        electronics: "Electronics",
        serviceCenter: "Service Center",
        warehouse: "Warehouse",
        distributor: "Distributor",
        salon: "Salon",
        custom: "Custom Business",
      },
      pkg: {
        eyebrow: "Pricing packages",
        title: "Monthly subscription or one-time purchase",
        intro:
          "Pick a plan — open Core Features, Customization and Website details inside each card, then WhatsApp us with that package.",
        tabsLabel: "Package billing type",
        tabMonthly: "Monthly Subscription",
        tabOnetime: "One-Time Purchase",
        regular: "Regular",
        offerOnboarding: "Offer Onboarding",
        offerPrice: "Offer Price",
        mostPopular: "Most Popular",
        label01: "Package 01",
        label02: "Package 02",
        label03: "Package 03",
        billingMonthly: "Monthly Subscription",
        billingOnetime: "One-Time Purchase",
        dropCore: "Core Features",
        dropCorePos: "POS Core Features",
        dropIncluded: "Included Features",
        dropCustom: "Customization",
        dropCustomBenefit: "Customization benefits",
        dropWebsite: "Website benefits",
        waGroupCore: "Core Features",
        waGroupCustom: "Customization",
        waGroupWebsite: "Website",
        waGroupIncluded: "Included Features",
        noteBefore: "Need help choosing?",
        noteLink: "Ask ONETECH on WhatsApp.",

        // Shared feature list items (monthly core)
        fDashboard: "Dashboard",
        fSalesMgmt: "Sales Management",
        fPurchaseMgmt: "Purchase Management",
        fStockInvMgmt: "Stock / Inventory Management",
        fAccounts: "Accounts",
        fQuotations: "Quotations",
        fProductMgmt: "Product Management",
        fCustomerSupplier: "Customer & Supplier / Peoples Management",
        fSalesPurchaseReturn: "Sales & Purchase Return",
        fStockTransfer: "Stock Transfer",
        fCrm: "CRM",
        fHrm: "HRM",
        fSmsMgmt: "SMS Management",
        fApiSettings: "API Settings",
        fReportsAnalytics: "Reports & Analytics",
        fGeneralSettings: "General Settings",
        fMultiBranch: "Multi-Branch Support",
        fLifetimeUpdate: "Lifetime Free Update",
        fLiveSupport: "Live support",
        fDataBackup: "Data Backup",

        // One-time shorter list items
        fSales: "Sales",
        fPurchase: "Purchase",
        fStockInv: "Stock / Inventory",
        fProducts: "Products",
        fCustomerSupplierShort: "Customer & Supplier Management",
        fReturn: "Return",
        fSms: "SMS",
        fReports: "Reports",
        fSettings: "Settings",
        fMultiBranchReady: "Multi-Branch Ready",
        fTransfers: "Transfers",
        fCompleteSoftware: "Complete OnePOS Software",
        fProfWebsite: "Professional Business Website",
        fMobileResponsive: "Mobile Responsive Website",
        fShowcase: "Product / Service Showcase",
        fContactWa: "Contact & WhatsApp Integration",
        fPosWebsiteInt: "POS + Website Integration",

        // Meta lines (numbers included)
        metaMonthlyBranch:
          "Monthly: <b>৳390</b>/month — 1 Branch",
        metaAdditionalBranch:
          "Additional Branch: <b>৳300</b>/month per Branch",
        metaAnnualRenewal: "Annual Renewal Fee: <b>Applicable</b>",
        dataMonthlyMeta:
          "৳390/month for 1 Branch; Additional Branch ৳300/month",
        dataAnnualMeta: "Annual Renewal Fee: Applicable",

        // BASIC monthly
        basicName: "BASIC POS",
        basicDesc:
          "A simple, complete POS solution for small and medium businesses",
        basicCta: "WhatsApp — BASIC POS",
        basicCustomNone: "Customization: None",
        basicCustomNote:
          "Standard/Core Features only — no custom development outside the existing system",

        // CUSTOM monthly
        customName: "CUSTOM POS",
        customDesc:
          "For businesses that need the POS customized to their own workflow",
        customCta: "WhatsApp — CUSTOM POS",
        customC1: "Customize the POS according to your business",
        customC2: "Small changes to existing features are possible",
        customC3: "Build custom workflows as needed",
        customC4: "Additional module development available",
        customC5: "Custom Module Development: from ৳5,000 / Module",
        customC6:
          "Pricing for minor customizations is set after discussing requirements",

        // POS + WEBSITE monthly
        webName: "POS + WEBSITE",
        webPackageAttr: "POS + BUSINESS WEBSITE",
        webDesc:
          "POS software and a business website together as a complete digital solution",
        webCta: "WhatsApp — POS + Website",
        webW1: "Professional Business Website",
        webW2: "Mobile Responsive Design",
        webW3: "Business Information & Services",
        webW4: "Product / Service Showcase",
        webW5: "Contact Information",
        webW6: "WhatsApp / Contact Integration",
        webW7: "Integration with POS as needed",
        webC1: "Full POS customization",
        webC2: "Website customization",
        webC3: "Development aligned to your business workflow",
        webC4: "Additional module development",
        webC5: "Custom Module Development: from ৳5,000 / Module",
        webC6: "Minor customization pricing subject to discussion",

        // BASIC one-time
        basicOtName: "BASIC POS — One Time",
        basicOtPackageAttr: "BASIC POS - ONE TIME",
        basicOtDesc:
          "Pay once for OnePOS — Standard/Core version",
        basicOtCta: "WhatsApp — BASIC One Time",
        basicOtC1: "Standard/Core Version only",
        basicOtC2: "Annual Renewal Fee: Applicable",

        // CUSTOM one-time
        customOtName: "CUSTOM POS — One Time",
        customOtPackageAttr: "CUSTOM POS - ONE TIME",
        customOtDesc: "One-time OnePOS purchase with full customization",
        customOtCta: "WhatsApp — CUSTOM One Time",
        customOtC1: "Full customization for your business",
        customOtC2: "Custom Workflow",
        customOtC3: "Feature Modification",
        customOtC4: "Additional Module Development",
        customOtC5: "Custom Module: from ৳5,000 / Module",
        customOtC6: "Minor customization charges subject to discussion",
        customOtC7: "Annual Renewal Fee: Applicable",

        // POS + WEBSITE one-time
        webOtName: "POS + WEBSITE — One Time",
        webOtPackageAttr: "POS + WEBSITE - ONE TIME",
        webOtDesc: "Complete OnePOS + Professional Business Website",
        webOtCta: "WhatsApp — POS + Website One Time",
        webOtW1: "Professional Business Website",
        webOtW2: "Mobile Responsive Website",
        webOtW3: "Product / Service Showcase",
        webOtW4: "Contact & WhatsApp Integration",
        webOtW5: "POS + Website Integration",
        webOtC1: "POS Full Customization",
        webOtC2: "Website Customization",
        webOtC3: "Custom Business Workflow",
        webOtC4: "Additional Feature Development",
        webOtC5: "Custom Module: from ৳5,000 / Module",
        webOtC6: "Minor customization charges subject to discussion",
        webOtC7: "Annual Renewal Fee: Applicable",
      },
      process: {
        eyebrow: "How it works",
        title: "From discussion to go-live",
        intro:
          "We understand your operation first, then configure OnePOS accordingly.",
        s1Title: "Requirement",
        s1Text:
          "Tell us how your business currently sells, manages stock and reports.",
        s2Title: "Setup",
        s2Text:
          "We configure modules, roles, products, invoices and workflow.",
        s3Title: "Training",
        s3Text:
          "Your team learns the system with practical business scenarios.",
        s4Title: "Go Live",
        s4Text: "Start using OnePOS with ONETECH support when you need it.",
      },
      contact: {
        eyebrow: "Ready to upgrade your business?",
        title: "Let’s build the right OnePOS setup for you.",
        text: "Send us your business type and requirements. Our team will help you choose the right package or prepare a custom solution.",
        wa: "Chat on WhatsApp",
        email: "Email Us",
        brandText:
          "Custom POS software designed and developed by ONETECH for businesses that need flexibility, visibility and control.",
        labelEmail: "Email",
        labelPhone: "Phone / WhatsApp",
        labelOffice: "Office",
        address:
          "Shop No 11, Sobuj Neel Tower,<br>Shah Kabir Mazar Road, Uttara,<br>Dhaka 1230",
      },
      footer: {
        copy: "© <span id=\"year\"></span> <a href=\"https://onetech.com.bd/\" target=\"_blank\" rel=\"noopener\">ONETECH</a>, all rights reserved.",
        backTop: "Back to top",
      },
      demo: {
        eyebrow: "Live demo",
        title: "Sign in with these credentials",
        text: "POS login page does not auto-fill from the URL yet. Copy below, then open the demo.",
        email: "Email",
        password: "Password",
        openLogin: "Open Demo Login",
        close: "Close",
        floatWa: "Chat on WhatsApp",
      },
      wa: {
        nav: "Hello ONETECH, I want to know more about OnePOS.",
        float: "Hello ONETECH, I want to know more about OnePOS.",
        business:
          "Hello ONETECH, I want to check whether OnePOS can be customized for my business.",
        packages: "Hello ONETECH, I need help choosing a OnePOS package.",
        cta: "Hello ONETECH, I want to get started with OnePOS.",
        mailSubject: "OnePOS Enquiry",
      },
      js: {
        hello: "Hello ONETECH,",
        interested: "I am interested in:",
        offerPrice: "Offer price:",
        regular: "Regular:",
        notes: "Notes:",
        closing: "Please share the next steps and demo details.",
        fallbackPackage: "OnePOS Package",
        copy: "Copy",
        copied: "Copied",
      },
    },

    bn: {
      meta: {
        title: "OnePOS by ONETECH | যেকোনো ব্যবসার জন্য স্মার্ট POS",
        description:
          "OnePOS by ONETECH — যেকোনো ব্যবসার জন্য তৈরি নমনীয় কাস্টম POS সমাধান।",
      },
      brand: {
        home: "OnePOS হোম",
      },
      nav: {
        features: "ফিচার",
        payments: "পেমেন্ট",
        businesses: "ব্যবসা",
        packages: "প্যাকেজ",
        contact: "যোগাযোগ",
        whatsapp: "হোয়াটসঅ্যাপে কথা বলুন",
        openMenu: "মেনু খুলুন",
        primary: "প্রধান নেভিগেশন",
      },
      hero: {
        eyebrow: "ONETECH-এর কাস্টম POS সমাধান",
        title: "ONEPOS",
        titleSpan: "আপনার ব্যবসার মতো করে তৈরি।",
        text: "রিটেইল, রেস্টুরেন্ট, ফার্মেসি, ওয়্যারহাউস ও ক্রমবর্ধমান চেইনের জন্য নমনীয় পয়েন্ট-অফ-সেল সিস্টেম — আপনি যেভাবে কাজ করেন সেভাবে কনফিগার করা।",
        ctaPackages: "প্যাকেজ দেখুন",
        ctaDemo: "ডেমো রিকোয়েস্ট করুন",
        point1: "কাস্টমাইজেবল মডিউল",
        point2: "মাল্টি-বিজনেস রেডি",
        point3: "লোকাল ONETECH সাপোর্ট",
        previewLabel: "OnePOS ইন্টারফেস প্রিভিউ",
      },
      mock: {
        dashboardTitle: "OnePOS ড্যাশবোর্ড",
        welcome: "স্বাগতম ফিরে",
        storeOverview: "স্টোর ওভারভিউ",
        today: "আজ",
        thisMonth: "এই মাস",
        totalSale: "মোট বিক্রয়",
        totalPurchase: "মোট ক্রয়",
        totalDue: "মোট বকেয়া",
        salesTrend: "বিক্রয় ট্রেন্ড",
        sevenDays: "৭ দিন",
        cashPosition: "ক্যাশ অবস্থান",
        paid: "পরিশোধিত",
        due: "বকেয়া",
        balance: "ব্যালেন্স",
        todaysSales: "আজকের বিক্রয়",
        inventory: "ইনভেন্টরি",
        stockSynced: "স্টক সিঙ্কড",
      },
      trust: {
        flexible: "নমনীয়",
        flexibleDesc: "আপনার ওয়ার্কফ্লোতে মানানসই",
        scalable: "স্কেলেবল",
        scalableDesc: "এক দোকান থেকে মাল্টি-ব্রাঞ্চ",
        complete: "সম্পূর্ণ",
        completeDesc: "বিক্রয় থেকে রিপোর্ট এক জায়গায়",
        supported: "সাপোর্টেড",
        supportedDesc: "ONETECH লোকাল টিম",
      },
      feat: {
        eyebrow: "কোর মডিউল",
        title: "প্রতিটি সেকশন কী করে",
        navLabel: "মডিউল ক্যাটাগরি",
        groupOps: "অপারেশনস",
        groupCatalog: "ক্যাটালগ ও পিপল",
        groupFinance: "ফাইন্যান্স",
        groupGrowth: "গ্রোথ ও টিম",
        groupSystem: "সিস্টেম",
        badgeOps: "অপারেশনস",
        badgeCatalog: "ক্যাটালগ ও পিপল",
        badgeFinance: "ফাইন্যান্স",
        badgeGrowth: "গ্রোথ ও টিম",
        badgeSystem: "সিস্টেম",

        navDashboard: "ড্যাশবোর্ড",
        navSale: "সেল",
        navPurchase: "পারচেজ",
        navStock: "স্টক",
        navReturn: "রিটার্ন",
        navTransfers: "ট্রান্সফার",
        navProducts: "প্রোডাক্টস",
        navPeoples: "পিপলস",
        navQuotations: "কোটেশন",
        navAccounts: "অ্যাকাউন্টস",
        navCrm: "CRM",
        navHrm: "HRM",
        navSms: "SMS",
        navSettings: "সেটিংস",
        navApi: "API সেটিংস",
        navReports: "রিপোর্টস",

        dashboardTitle: "ড্যাশবোর্ড",
        dashboardDesc:
          "আপনার স্টোর ওভারভিউ — লাইভ সংখ্যা, শর্টকাট এবং প্রয়োজনে সাহায্য।",
        dashboardCard1: "ড্যাশবোর্ড",
        dashboardCard1Desc:
          "এক নজরে বিক্রয়, ক্রয়, বকেয়া, লাভ ও ক্যাশ অবস্থান দেখুন।",
        dashboardCard2: "হেল্প ও FAQ",
        dashboardCard2Desc:
          "সাধারণ OnePOS কাজের দ্রুত উত্তর ও নির্দেশনা।",

        saleTitle: "সেল",
        saleDesc:
          "কাউন্টার দ্রুত চালান — বিল করুন, অফার ম্যানেজ করুন এবং ডেলিভারি এক জায়গা থেকে হ্যান্ডেল করুন।",
        saleQuick: "কুইক সেল",
        saleQuickDesc:
          "ব্যস্ত কাউন্টারের জন্য বারকোড ও হোল্ড সাপোর্টসহ দ্রুত চেকআউট।",
        saleList: "সেল লিস্ট",
        saleListDesc: "প্রতিটি পুরনো সেলস ইনভয়েস খুঁজুন, দেখুন ও ম্যানেজ করুন।",
        saleGift: "গিফট কার্ড",
        saleGiftDesc: "স্টোর গিফট কার্ড ইস্যু, ট্র্যাক ও রিডিম করুন।",
        saleCoupons: "কুপন",
        saleCouponsDesc: "প্রমোশনের জন্য ডিসকাউন্ট কোড তৈরি ও নিয়ন্ত্রণ করুন।",
        saleReward: "রিওয়ার্ড পয়েন্টস",
        saleRewardDesc:
          "লয়্যালটি পয়েন্ট চালু করুন যাতে রিপিট কাস্টমার অর্জন ও রিডিম করতে পারে।",
        saleDelivery: "ডেলিভারি",
        saleDeliveryDesc: "ডেলিভারি অর্ডার ও হ্যান্ডঅফ স্ট্যাটাস ম্যানেজ করুন।",

        purchaseTitle: "পারচেজ",
        purchaseDesc:
          "সাপ্লায়ার ক্রয় রেকর্ড করুন এবং কস্টিং ও স্টকের জন্য পারচেজ ইতিহাস গুছানো রাখুন।",
        purchaseList: "পারচেজ লিস্ট",
        purchaseListDesc: "সব সাপ্লায়ার পারচেজ ও খরচ ব্রাউজ ও ফিল্টার করুন।",
        purchaseAdd: "অ্যাড পারচেজ",
        purchaseAddDesc:
          "আইটেম, পরিমাণ ও সাপ্লায়ার বিবরণসহ নতুন পারচেজ এন্টার করুন।",

        stockTitle: "স্টক",
        stockDesc: "হাতে কী আছে এবং সময়ের সাথে স্টক কীভাবে নড়েছে জানুন।",
        stockInventory: "ইনভেন্টরি লিস্ট",
        stockInventoryDesc:
          "প্রোডাক্ট অনুযায়ী লাইভ পরিমাণ, অ্যাভেইলেবিলিটি ও স্টক ভ্যালু।",
        stockHistory: "স্টক হিস্ট্রি",
        stockHistoryDesc:
          "যোগ, কাটা ও অ্যাডজাস্টমেন্ট সময়ের সাথে ট্র্যাক করুন।",

        returnTitle: "রিটার্ন",
        returnDesc:
          "কাস্টমার ও সাপ্লায়ার উভয়ের জন্য প্রোডাক্ট রিটার্ন পরিষ্কারভাবে হ্যান্ডেল করুন।",
        returnSale: "সেল রিটার্ন",
        returnSaleDesc:
          "কাস্টমার ফেরত আনা আইটেম প্রসেস করুন এবং স্টক আপডেট করুন।",
        returnPurchase: "পারচেজ রিটার্ন",
        returnPurchaseDesc:
          "সাপ্লায়ারকে পণ্য ফেরত দিন এবং পারচেজ রেকর্ড অ্যাডজাস্ট করুন।",

        transfersTitle: "ট্রান্সফার",
        transfersDesc:
          "ব্রাঞ্চ বা ওয়্যারহাউসের মধ্যে স্টক সরান, হিসাব হারানো ছাড়াই।",
        transfersList: "ট্রান্সফার",
        transfersListDesc: "পেন্ডিং ও সম্পন্ন স্টক মুভমেন্ট দেখুন।",
        transfersAdd: "অ্যাড ট্রান্সফার",
        transfersAddDesc: "নতুন ট্রান্সফার তৈরি করুন এবং ইনভেন্টরি লাইভ আপডেট করুন।",

        productsTitle: "প্রোডাক্টস",
        productsDesc:
          "ক্যাটালগ তৈরি ও সাজান — আইটেম, ক্যাটাগরি, ব্র্যান্ড ও বারকোড।",
        productsList: "প্রোডাক্ট লিস্ট",
        productsListDesc: "সব প্রোডাক্ট এক জায়গায় খুঁজুন, ফিল্টার ও ম্যানেজ করুন।",
        productsCreate: "ক্রিয়েট প্রোডাক্ট",
        productsCreateDesc: "মূল্য, ট্যাক্স, SKU ও স্টকসহ নতুন আইটেম যোগ করুন।",
        productsBulk: "বাল্ক প্রোডাক্ট ইমপোর্ট",
        productsBulkDesc: "স্প্রেডশিট দিয়ে একসাথে অনেক প্রোডাক্ট আপলোড করুন।",
        productsCategories: "ক্যাটাগরি",
        productsCategoriesDesc: "দ্রুত বিলিং ও রিপোর্টের জন্য প্রোডাক্ট গ্রুপ করুন।",
        productsBrands: "ব্র্যান্ডস",
        productsBrandsDesc: "আইটেমে ম্যানুফ্যাকচারার বা ব্র্যান্ড নাম ম্যানেজ করুন।",
        productsBarcode: "প্রিন্ট বারকোড",
        productsBarcodeDesc: "স্ক্যানযোগ্য প্রোডাক্ট লেবেল তৈরি ও প্রিন্ট করুন।",

        peoplesTitle: "পিপলস",
        peoplesDesc:
          "গ্রুপ ও সম্পূর্ণ প্রোফাইলসহ কাস্টমার ও সাপ্লায়ার গুছানো রাখুন।",
        peoplesGroups: "কাস্টমার গ্রুপস",
        peoplesGroupsDesc: "ক্লায়েন্ট সেগমেন্ট করুন (রিটেইল, হোলসেল ইত্যাদি)।",
        peoplesCustomers: "কাস্টমারস",
        peoplesCustomersDesc: "ক্রেতাদের প্রোফাইল, ইতিহাস ও যোগাযোগের বিবরণ।",
        peoplesSuppliers: "সাপ্লায়ারস",
        peoplesSuppliersDesc: "ক্রয় ও বকেয়ার জন্য ভেন্ডর রেকর্ড।",

        quotationsTitle: "কোটেশন",
        quotationsDesc:
          "কাস্টমারকে মূল্য অনুমান পাঠান, তারপর গৃহীত কোটকে সেলে রূপান্তর করুন।",
        quotationsAdd: "অ্যাড কোটেশন",
        quotationsAddDesc:
          "প্রোডাক্ট ও প্রাইসিংসহ নতুন কোট তৈরি ও পাঠান।",
        quotationsList: "কোটেশন লিস্ট",
        quotationsListDesc: "কোট স্ট্যাটাস ট্র্যাক করুন, ফলোআপ করুন ও সেলে কনভার্ট করুন।",

        accountsTitle: "অ্যাকাউন্টস",
        accountsDesc:
          "টাকা আসা-যাওয়া ফলো করুন — অ্যাকাউন্ট, স্টেটমেন্ট, খরচ ও ব্যালেন্স।",
        accountsView: "ভিউ অ্যাকাউন্টস",
        accountsViewDesc: "ক্যাশ, ব্যাংক ও অন্যান্য আর্থিক অ্যাকাউন্ট ম্যানেজ করুন।",
        accountsStatement: "স্টেটমেন্ট",
        accountsStatementDesc: "যেকোনো তারিখ রেঞ্জে অ্যাকাউন্ট স্টেটমেন্ট তৈরি করুন।",
        accountsBalance: "ব্যালেন্স শিট",
        accountsBalanceDesc:
          "সম্পদ, দায় ও সামগ্রিক আর্থিক অবস্থান দেখুন।",
        accountsExpenses: "ভিউ এক্সপেন্সেস",
        accountsExpensesDesc: "সব ব্যবসায়িক খরচ এক তালিকায় ট্র্যাক করুন।",
        accountsExpenseCat: "এক্সপেন্স ক্যাটাগরি",
        accountsExpenseCatDesc:
          "ভাড়া, ইউটিলিটি, স্টক ইত্যাদি অনুযায়ী খরচ সাজান।",

        crmTitle: "CRM",
        crmDesc: "লিড ধরুন এবং কাস্টমারে পরিণত হওয়া পর্যন্ত ফলো করুন।",
        crmLeads: "লিডস",
        crmLeadsDesc: "পাইপলাইনে সম্ভাব্য কাস্টমার ট্র্যাক করুন।",
        crmSource: "লিড সোর্স",
        crmSourceDesc: "লিড কোথা থেকে আসে দেখুন — অ্যাডস, রেফারেল, ওয়াক-ইন।",
        crmStatus: "লিড স্ট্যাটাস",
        crmStatusDesc: "নতুন, যোগাযোগকৃত, জয় ইত্যাদি ধাপে লিড সরান।",
        crmNotes: "লিড নোটস",
        crmNotesDesc: "প্রতিটি লিডের বিপরীতে ফলোআপ নোট সংরক্ষণ করুন।",

        hrmTitle: "HRM",
        hrmDesc: "স্টাফ, উপস্থিতি, ডিপার্টমেন্ট ও পে রোলের বেসিক ম্যানেজ করুন।",
        hrmEmployee: "এমপ্লয়ি",
        hrmEmployeeDesc: "স্টাফ প্রোফাইল ও কর্মসংস্থান রেকর্ড।",
        hrmAttendance: "অ্যাটেনডেন্স",
        hrmAttendanceDesc: "কে কখন এসেছে ট্র্যাক করুন।",
        hrmDepartment: "ডিপার্টমেন্ট",
        hrmDepartmentDesc: "ডিপার্টমেন্ট অনুযায়ী টিম সাজান।",
        hrmPayrolls: "পেরোলস",
        hrmPayrollsDesc: "বেতন প্রসেসিং ও পেরোল রেকর্ড।",

        smsTitle: "SMS",
        smsDesc:
          "পুনর্ব্যবহারযোগ্য টেমপ্লেট ও সম্পূর্ণ পাঠানোর ইতিহাসসহ কাস্টমার ও স্টাফকে মেসেজ করুন।",
        smsLogs: "SMS লগস",
        smsLogsDesc: "পাঠানো মেসেজের ইতিহাস ও ডেলিভারি স্ট্যাটাস।",
        smsTemplates: "SMS টেমপ্লেটস",
        smsTemplatesDesc:
          "ইনভয়েস, রিমাইন্ডার ও অফারের জন্য পুনর্ব্যবহারযোগ্য টেক্সট সংরক্ষণ করুন।",

        settingsTitle: "সেটিংস",
        settingsDesc:
          "স্টোর, ব্র্যান্ডিং, প্রিন্ট, ইউজার, ব্রাঞ্চ ও সিস্টেম সিকিউরিটি কনফিগার করুন।",
        settingsGeneral: "জেনারেল সেটিংস",
        settingsGeneralDesc:
          "দোকানের নাম, যোগাযোগ, ঠিকানা, লোগো ও টাইমজোন।",
        settingsColor: "কালার সেটিংস",
        settingsColorDesc: "অ্যাপের চেহারা আপনার ব্র্যান্ড রঙে মিলান।",
        settingsPrint: "প্রিন্ট সেটিংস",
        settingsPrintDesc: "ইনভয়েস/রিসিপ্ট টেমপ্লেট ও প্রিন্টার সেটআপ।",
        settingsTerms: "টার্মস ও কন্ডিশনস",
        settingsTermsDesc: "কাস্টমার ডকুমেন্টে যে লেখা দেখা যায়।",
        settingsMail: "মেইল সেটিংস",
        settingsMailDesc: "ইনভয়েস, রিপোর্ট ও অ্যালার্টের জন্য SMTP সেটআপ।",
        settingsBranches: "ব্রাঞ্চেস",
        settingsBranchesDesc: "এক সিস্টেম থেকে একাধিক আউটলেট ম্যানেজ করুন।",
        settingsRoles: "ভূমিকা ব্যবস্থাপনা",
        settingsRolesDesc:
          "অ্যাডমিন, ক্যাশিয়ার, ম্যানেজার ও অন্যান্য পারমিশন নির্ধারণ করুন।",
        settingsUsers: "ম্যানেজ ইউজারস",
        settingsUsersDesc: "স্টাফ অ্যাকাউন্ট যোগ করুন এবং ভূমিকা ও ব্রাঞ্চ দিন।",
        settingsUnits: "ইউনিটস",
        settingsUnitsDesc: "কেজি, পিস, লিটার ইত্যাদি প্রোডাক্ট ইউনিট সেট করুন।",
        settingsCurrencies: "কারেন্সিস",
        settingsCurrenciesDesc: "প্রাইমারি কারেন্সি ও এক্সচেঞ্জ অপশন।",
        settingsLogs: "অ্যাক্টিভিটিস লগস",
        settingsLogsDesc: "সিকিউরিটির জন্য ইউজার অ্যাকশনের অডিট ট্রেইল।",

        apiTitle: "API সেটিংস",
        apiDesc: "SMS গেটওয়ে, কুরিয়ার ও ফ্রড চেকের সাথে OnePOS সংযুক্ত করুন।",
        apiSms: "SMS সেটিংস",
        apiSmsDesc: "অ্যালার্ট ও কনফার্মেশনের জন্য SMS গেটওয়ে কনফিগার করুন।",
        apiCourier: "কুরিয়ার সেটিংস",
        apiCourierDesc: "শিপিং ও ট্র্যাকিংয়ের জন্য কুরিয়ার পার্টনার লিঙ্ক করুন।",
        apiFraud: "ফ্রড চেক API",
        apiFraudDesc: "ফ্রড ডিটেকশন টুল দিয়ে ঝুঁকিপূর্ণ অর্ডার চিহ্নিত করুন।",

        reportsTitle: "রিপোর্টস",
        reportsDesc:
          "বিক্রয়, স্টক, পিপল ও ব্রাঞ্চ জুড়ে পারফরম্যান্স বুঝুন।",
        reportsSummary: "সামারি রিপোর্টস",
        reportsSummaryDesc: "এক ভিউতে উচ্চ-স্তরের ব্যবসায়িক পারফরম্যান্স।",
        reportsSales: "সেলস রিপোর্ট",
        reportsSalesDesc: "রেভিনিউ, ইনভয়েস ও প্রোডাক্ট মুভমেন্ট।",
        reportsPurchases: "পারচেজ রিপোর্ট",
        reportsPurchasesDesc: "সাপ্লায়ার ক্রয় ও পারচেজ খরচ।",
        reportsCustomerLedger: "কাস্টমার লেজার রিপোর্ট",
        reportsCustomerLedgerDesc:
          "প্রতি কাস্টমারের ব্যালেন্স, পেমেন্ট ও বকেয়া।",
        reportsSupplierLedger: "সাপ্লায়ার লেজার রিপোর্ট",
        reportsSupplierLedgerDesc:
          "প্রতি সাপ্লায়ারের ব্যালেন্স ও পেমেন্ট ইতিহাস।",
        reportsInventory: "ইনভেন্টরি রিপোর্ট",
        reportsInventoryDesc: "স্টক লেভেল, ভ্যালু ও মুভমেন্ট।",
        reportsEmployees: "এমপ্লয়িজ রিপোর্ট",
        reportsEmployeesDesc: "স্টাফ পারফরম্যান্স ও সংশ্লিষ্ট HR তথ্য।",
        reportsBranch: "ব্রাঞ্চ রিপোর্ট",
        reportsBranchDesc: "আউটলেটগুলোর ফলাফল তুলনা করুন।",
      },
      pay: {
        eyebrow: "পেমেন্ট সিস্টেম",
        title: "প্রতিটি পেমেন্ট। একদম পরিষ্কার।",
        intro:
          "বাংলাদেশের রিটেইলের জন্য তৈরি — কাস্টমার যেভাবে পেমেন্ট করে সেভাবে নিন, তারপর পরিশোধিত, বকেয়া ও ব্যালেন্স এক জায়গায় দেখুন।",
        h1: "পার্শিয়াল পেমেন্ট ও অবশিষ্ট বকেয়া",
        h2: "কাস্টমার বকেয়া গ্রহণ ও পারচেজ বকেয়া",
        h3: "প্রতিটি বিলে পরিশোধিত বনাম অপরিশোধিত দৃশ্যমানতা",
        h4: "অ্যাকাউন্ট ব্যালেন্স ক্যাশ ফ্লোর সাথে সিঙ্কে থাকে",
        methodsLabel: "সাপোর্টেড পেমেন্ট মেথড",
        cash: "ক্যাশ",
        card: "কার্ড",
        mobileBanking: "মোবাইল ব্যাংকিং",
        bankTransfer: "ব্যাংক ট্রান্সফার",
        dueCredit: "বকেয়া / ক্রেডিট",
        totalPaid: "মোট পরিশোধিত",
        totalDue: "মোট বকেয়া",
        purchaseDue: "পারচেজ বকেয়া",
        accountBalance: "অ্যাকাউন্ট ব্যালেন্স",
        note: "ক্যাশ, কার্ড, বিকাশ, নগদ ও অন্যান্য মোবাইল ওয়ালেট — আপনার স্টোরের জন্য কনফিগার করা।",
      },
      biz: {
        eyebrow: "আসল ব্যবসার জন্য তৈরি",
        title: "OnePOS প্রায় যেকোনো ব্যবসায়িক মডেলে মানিয়ে নিতে পারে",
        text: "আপনার ব্যবসাকে নির্দিষ্ট সফটওয়্যার ফ্লোতে জোর না করে, OnePOS আপনার প্রোডাক্ট, স্টাফ, ব্রাঞ্চ, রিপোর্টিং ও অপারেশনাল চাহিদা অনুযায়ী কনফিগার করা যায়।",
        link: "আপনার ব্যবসার সেটআপ নিয়ে আলোচনা করুন →",
        retail: "রিটেইল শপ",
        restaurant: "রেস্টুরেন্ট",
        pharmacy: "ফার্মেসি",
        superShop: "সুপার শপ",
        wholesale: "হোলসেল",
        fashion: "ফ্যাশন",
        electronics: "ইলেকট্রনিক্স",
        serviceCenter: "সার্ভিস সেন্টার",
        warehouse: "ওয়্যারহাউস",
        distributor: "ডিস্ট্রিবিউটর",
        salon: "সেলুন",
        custom: "কাস্টম বিজনেস",
      },
      pkg: {
        eyebrow: "প্রাইসিং প্যাকেজ",
        title: "মাসিক সাবস্ক্রিপশন অথবা এককালীন ক্রয়",
        intro:
          "একটি প্ল্যান বেছে নিন — প্রতিটি কার্ডে কোর ফিচার, কাস্টমাইজেশন ও ওয়েবসাইটের বিবরণ খুলুন, তারপর সেই প্যাকেজ নিয়ে হোয়াটসঅ্যাপে যোগাযোগ করুন।",
        tabsLabel: "প্যাকেজ বিলিং টাইপ",
        tabMonthly: "মাসিক সাবস্ক্রিপশন",
        tabOnetime: "এককালীন ক্রয়",
        regular: "রেগুলার",
        offerOnboarding: "অফার অনবোর্ডিং",
        offerPrice: "অফার প্রাইস",
        mostPopular: "সবচেয়ে জনপ্রিয়",
        label01: "প্যাকেজ ০১",
        label02: "প্যাকেজ ০২",
        label03: "প্যাকেজ ০৩",
        billingMonthly: "মাসিক সাবস্ক্রিপশন",
        billingOnetime: "এককালীন ক্রয়",
        dropCore: "কোর ফিচার",
        dropCorePos: "POS কোর ফিচার",
        dropIncluded: "অন্তর্ভুক্ত ফিচার",
        dropCustom: "কাস্টমাইজেশন",
        dropCustomBenefit: "কাস্টমাইজেশন সুবিধা",
        dropWebsite: "ওয়েবসাইট সুবিধা",
        waGroupCore: "কোর ফিচার",
        waGroupCustom: "কাস্টমাইজেশন",
        waGroupWebsite: "ওয়েবসাইট",
        waGroupIncluded: "অন্তর্ভুক্ত ফিচার",
        noteBefore: "কোনটি বেছে নেবেন বুঝতে পারছেন না?",
        noteLink: "হোয়াটসঅ্যাপে ONETECH-কে জিজ্ঞাসা করুন।",

        fDashboard: "ড্যাশবোর্ড",
        fSalesMgmt: "সেলস ম্যানেজমেন্ট",
        fPurchaseMgmt: "পারচেজ ম্যানেজমেন্ট",
        fStockInvMgmt: "স্টক / ইনভেন্টরি ম্যানেজমেন্ট",
        fAccounts: "অ্যাকাউন্টস",
        fQuotations: "কোটেশন",
        fProductMgmt: "প্রোডাক্ট ম্যানেজমেন্ট",
        fCustomerSupplier: "কাস্টমার ও সাপ্লায়ার / পিপলস ম্যানেজমেন্ট",
        fSalesPurchaseReturn: "সেলস ও পারচেজ রিটার্ন",
        fStockTransfer: "স্টক ট্রান্সফার",
        fCrm: "CRM",
        fHrm: "HRM",
        fSmsMgmt: "SMS ম্যানেজমেন্ট",
        fApiSettings: "API সেটিংস",
        fReportsAnalytics: "রিপোর্টস ও অ্যানালিটিক্স",
        fGeneralSettings: "জেনারেল সেটিংস",
        fMultiBranch: "মাল্টি-ব্রাঞ্চ সাপোর্ট",
        fLifetimeUpdate: "লাইফটাইম ফ্রি আপডেট",
        fLiveSupport: "লাইভ সাপোর্ট",
        fDataBackup: "ডেটা ব্যাকআপ",

        fSales: "সেলস",
        fPurchase: "পারচেজ",
        fStockInv: "স্টক / ইনভেন্টরি",
        fProducts: "প্রোডাক্টস",
        fCustomerSupplierShort: "কাস্টমার ও সাপ্লায়ার ম্যানেজমেন্ট",
        fReturn: "রিটার্ন",
        fSms: "SMS",
        fReports: "রিপোর্টস",
        fSettings: "সেটিংস",
        fMultiBranchReady: "মাল্টি-ব্রাঞ্চ রেডি",
        fTransfers: "ট্রান্সফার",
        fCompleteSoftware: "সম্পূর্ণ OnePOS সফটওয়্যার",
        fProfWebsite: "প্রফেশনাল বিজনেস ওয়েবসাইট",
        fMobileResponsive: "মোবাইল রেসপন্সিভ ওয়েবসাইট",
        fShowcase: "প্রোডাক্ট / সার্ভিস শোকেস",
        fContactWa: "যোগাযোগ ও হোয়াটসঅ্যাপ ইন্টিগ্রেশন",
        fPosWebsiteInt: "POS + ওয়েবসাইট ইন্টিগ্রেশন",

        metaMonthlyBranch: "মাসিক: <b>৳390</b>/মাস — ১টি ব্রাঞ্চ",
        metaAdditionalBranch:
          "অতিরিক্ত ব্রাঞ্চ: <b>৳300</b>/মাস প্রতি ব্রাঞ্চ",
        metaAnnualRenewal: "বার্ষিক রিনিউয়াল ফি: <b>প্রযোজ্য</b>",
        dataMonthlyMeta:
          "১টি ব্রাঞ্চের জন্য ৳390/মাস; অতিরিক্ত ব্রাঞ্চ ৳300/মাস",
        dataAnnualMeta: "বার্ষিক রিনিউয়াল ফি: প্রযোজ্য",

        basicName: "BASIC POS",
        basicDesc: "ছোট ও মাঝারি ব্যবসার জন্য সহজ ও সম্পূর্ণ POS সমাধান",
        basicCta: "হোয়াটসঅ্যাপ — BASIC POS",
        basicCustomNone: "কাস্টমাইজেশন: নেই",
        basicCustomNote:
          "শুধু স্ট্যান্ডার্ড/কোর ফিচার — বিদ্যমান সিস্টেমের বাইরে কোনো কাস্টম ডেভেলপমেন্ট নেই",

        customName: "CUSTOM POS",
        customDesc:
          "যেসব ব্যবসার নিজস্ব ওয়ার্কফ্লো অনুযায়ী POS কাস্টমাইজ করা প্রয়োজন",
        customCta: "হোয়াটসঅ্যাপ — CUSTOM POS",
        customC1: "ব্যবসা অনুযায়ী POS কাস্টমাইজ করার সুবিধা",
        customC2: "বিদ্যমান ফিচারের ছোটখাটো পরিবর্তন করা যাবে",
        customC3: "প্রয়োজন অনুযায়ী কাস্টম ওয়ার্কফ্লো তৈরি করা যাবে",
        customC4: "অতিরিক্ত মডিউল ডেভেলপমেন্ট করা যাবে",
        customC5: "কাস্টম মডিউল ডেভেলপমেন্ট: ৳5,000 থেকে / মডিউল",
        customC6:
          "ছোটখাটো কাস্টমাইজেশনের মূল্য রিকয়ারমেন্ট আলোচনা করে নির্ধারণ",

        webName: "POS + WEBSITE",
        webPackageAttr: "POS + BUSINESS WEBSITE",
        webDesc:
          "POS সফটওয়্যার এবং বিজনেস ওয়েবসাইট একসাথে সম্পূর্ণ ডিজিটাল সমাধান",
        webCta: "হোয়াটসঅ্যাপ — POS + Website",
        webW1: "প্রফেশনাল বিজনেস ওয়েবসাইট",
        webW2: "মোবাইল রেসপন্সিভ ডিজাইন",
        webW3: "বিজনেস ইনফরমেশন ও সার্ভিসেস",
        webW4: "প্রোডাক্ট / সার্ভিস শোকেস",
        webW5: "যোগাযোগের তথ্য",
        webW6: "হোয়াটসঅ্যাপ / কন্ট্যাক্ট ইন্টিগ্রেশন",
        webW7: "POS-এর সাথে প্রয়োজন অনুযায়ী ইন্টিগ্রেশন",
        webC1: "POS পূর্ণ কাস্টমাইজেশন সুবিধা",
        webC2: "ওয়েবসাইট কাস্টমাইজেশন সুবিধা",
        webC3: "বিজনেস ওয়ার্কফ্লো অনুযায়ী ডেভেলপমেন্ট",
        webC4: "অতিরিক্ত মডিউল ডেভেলপমেন্ট",
        webC5: "কাস্টম মডিউল ডেভেলপমেন্ট: ৳5,000 থেকে / মডিউল",
        webC6: "ছোটখাটো কাস্টমাইজেশনের মূল্য আলোচনা সাপেক্ষে",

        basicOtName: "BASIC POS — এককালীন",
        basicOtPackageAttr: "BASIC POS - ONE TIME",
        basicOtDesc: "একবার পেমেন্ট করে OnePOS নিতে চান — স্ট্যান্ডার্ড/কোর ভার্সন",
        basicOtCta: "হোয়াটসঅ্যাপ — BASIC One Time",
        basicOtC1: "শুধু স্ট্যান্ডার্ড/কোর ভার্সন",
        basicOtC2: "বার্ষিক রিনিউয়াল ফি: প্রযোজ্য",

        customOtName: "CUSTOM POS — এককালীন",
        customOtPackageAttr: "CUSTOM POS - ONE TIME",
        customOtDesc: "পূর্ণ কাস্টমাইজেশনসহ এককালীন OnePOS ক্রয়",
        customOtCta: "হোয়াটসঅ্যাপ — CUSTOM One Time",
        customOtC1: "ব্যবসা অনুযায়ী পূর্ণ কাস্টমাইজেশন",
        customOtC2: "কাস্টম ওয়ার্কফ্লো",
        customOtC3: "ফিচার পরিবর্তন",
        customOtC4: "অতিরিক্ত মডিউল ডেভেলপমেন্ট",
        customOtC5: "কাস্টম মডিউল: ৳5,000 থেকে / মডিউল",
        customOtC6: "ছোটখাটো কাস্টমাইজেশনের চার্জ আলোচনা সাপেক্ষে",
        customOtC7: "বার্ষিক রিনিউয়াল ফি: প্রযোজ্য",

        webOtName: "POS + WEBSITE — এককালীন",
        webOtPackageAttr: "POS + WEBSITE - ONE TIME",
        webOtDesc: "সম্পূর্ণ OnePOS + প্রফেশনাল বিজনেস ওয়েবসাইট",
        webOtCta: "হোয়াটসঅ্যাপ — POS + Website One Time",
        webOtW1: "প্রফেশনাল বিজনেস ওয়েবসাইট",
        webOtW2: "মোবাইল রেসপন্সিভ ওয়েবসাইট",
        webOtW3: "প্রোডাক্ট / সার্ভিস শোকেস",
        webOtW4: "যোগাযোগ ও হোয়াটসঅ্যাপ ইন্টিগ্রেশন",
        webOtW5: "POS + ওয়েবসাইট ইন্টিগ্রেশন",
        webOtC1: "POS পূর্ণ কাস্টমাইজেশন",
        webOtC2: "ওয়েবসাইট কাস্টমাইজেশন",
        webOtC3: "কাস্টম বিজনেস ওয়ার্কফ্লো",
        webOtC4: "অতিরিক্ত ফিচার ডেভেলপমেন্ট",
        webOtC5: "কাস্টম মডিউল: ৳5,000 থেকে / মডিউল",
        webOtC6: "ছোটখাটো কাস্টমাইজেশনের চার্জ আলোচনা সাপেক্ষে",
        webOtC7: "বার্ষিক রিনিউয়াল ফি: প্রযোজ্য",
      },
      process: {
        eyebrow: "কীভাবে কাজ করে",
        title: "আলোচনা থেকে গো-লাইভ পর্যন্ত",
        intro:
          "আগে আপনার অপারেশন বুঝি, তারপর সে অনুযায়ী OnePOS কনফিগার করি।",
        s1Title: "রিকয়ারমেন্ট",
        s1Text:
          "আপনার ব্যবসা বর্তমানে কীভাবে বিক্রি, স্টক ও রিপোর্ট ম্যানেজ করে তা জানান।",
        s2Title: "সেটআপ",
        s2Text:
          "আমরা মডিউল, ভূমিকা, প্রোডাক্ট, ইনভয়েস ও ওয়ার্কফ্লো কনফিগার করি।",
        s3Title: "ট্রেনিং",
        s3Text:
          "ব্যবহারিক ব্যবসায়িক দৃশ্যপটে আপনার টিম সিস্টেম শেখে।",
        s4Title: "গো লাইভ",
        s4Text:
          "প্রয়োজনে ONETECH সাপোর্টসহ OnePOS ব্যবহার শুরু করুন।",
      },
      contact: {
        eyebrow: "আপনার ব্যবসা আপগ্রেড করতে প্রস্তুত?",
        title: "আসুন আপনার জন্য সঠিক OnePOS সেটআপ তৈরি করি।",
        text: "আপনার ব্যবসার ধরন ও রিকয়ারমেন্ট পাঠান। আমাদের টিম সঠিক প্যাকেজ বেছে নিতে বা কাস্টম সমাধান প্রস্তুত করতে সাহায্য করবে।",
        wa: "হোয়াটসঅ্যাপে চ্যাট করুন",
        email: "ইমেইল করুন",
        brandText:
          "নমনীয়তা, দৃশ্যমানতা ও নিয়ন্ত্রণ প্রয়োজন এমন ব্যবসার জন্য ONETECH কর্তৃক ডিজাইন ও ডেভেলপ করা কাস্টম POS সফটওয়্যার।",
        labelEmail: "ইমেইল",
        labelPhone: "ফোন / হোয়াটসঅ্যাপ",
        labelOffice: "অফিস",
        address:
          "শপ নং ১১, সবুজ নীল টাওয়ার,<br>শাহ কবির মাজার রোড, উত্তরা,<br>ঢাকা ১২৩০",
      },
      footer: {
        copy: "© <span id=\"year\"></span> <a href=\"https://onetech.com.bd/\" target=\"_blank\" rel=\"noopener\">ONETECH</a>, সর্বস্বত্ব সংরক্ষিত।",
        backTop: "উপরে ফিরে যান",
      },
      demo: {
        eyebrow: "লাইভ ডেমো",
        title: "এই ক্রেডেনশিয়াল দিয়ে সাইন ইন করুন",
        text: "POS লগইন পেজ এখনও URL থেকে অটো-ফিল করে না। নিচের তথ্য কপি করুন, তারপর ডেমো খুলুন।",
        email: "ইমেইল",
        password: "পাসওয়ার্ড",
        openLogin: "ডেমো লগইন খুলুন",
        close: "বন্ধ করুন",
        floatWa: "হোয়াটসঅ্যাপে চ্যাট করুন",
      },
      wa: {
        nav: "হ্যালো ONETECH, আমি OnePOS সম্পর্কে আরও জানতে চাই।",
        float: "হ্যালো ONETECH, আমি OnePOS সম্পর্কে আরও জানতে চাই।",
        business:
          "হ্যালো ONETECH, আমি জানতে চাই OnePOS আমার ব্যবসার জন্য কাস্টমাইজ করা যায় কি না।",
        packages: "হ্যালো ONETECH, OnePOS প্যাকেজ বেছে নিতে আমার সাহায্য দরকার।",
        cta: "হ্যালো ONETECH, আমি OnePOS দিয়ে শুরু করতে চাই।",
        mailSubject: "OnePOS অনুসন্ধান",
      },
      js: {
        hello: "হ্যালো ONETECH,",
        interested: "আমি আগ্রহী:",
        offerPrice: "অফার প্রাইস:",
        regular: "রেগুলার:",
        notes: "নোটস:",
        closing: "অনুগ্রহ করে পরবর্তী ধাপ ও ডেমোর বিবরণ শেয়ার করুন।",
        fallbackPackage: "OnePOS প্যাকেজ",
        copy: "কপি",
        copied: "কপি হয়েছে",
      },
    },
  };

  var currentLang = DEFAULT_LANG;

  function resolvePath(obj, path) {
    if (!obj || !path) return undefined;
    var parts = String(path).split(".");
    var cur = obj;
    for (var i = 0; i < parts.length; i++) {
      if (cur == null || typeof cur !== "object") return undefined;
      cur = cur[parts[i]];
    }
    return cur;
  }

  function t(key, lang) {
    var code = lang || currentLang || DEFAULT_LANG;
    var val = resolvePath(STRINGS[code], key);
    if (val == null && code !== DEFAULT_LANG) {
      val = resolvePath(STRINGS[DEFAULT_LANG], key);
    }
    return val != null ? val : key;
  }

  function getLang() {
    return currentLang;
  }

  function readLocaleCookie() {
    try {
      var match = document.cookie.match(/(?:^|; )pos_lang=(en|bn)(?:;|$)/);
      return match ? match[1] : null;
    } catch (_) {
      return null;
    }
  }

  function writeLocaleCookie(lang) {
    try {
      document.cookie = "pos_lang=" + lang + ";path=/;max-age=31536000;SameSite=Lax";
    } catch (_) {}
  }

  function setLang(lang) {
    if (!SUPPORTED[lang]) lang = DEFAULT_LANG;
    try {
      localStorage.setItem(STORAGE_KEY, lang);
    } catch (_) {}
    writeLocaleCookie(lang);
    applyLang(lang);
  }

  function buildWaHref(message) {
    return (
      "https://wa.me/" +
      WA_NUMBER +
      "?text=" +
      encodeURIComponent(message)
    );
  }

  function applyAttrPair(el, attr, key) {
    attr = (attr || "").trim();
    key = (key || "").trim();
    if (!attr || !key) return;

    var value = t(key);
    if (
      attr === "href" &&
      key.indexOf("wa.") === 0 &&
      key !== "wa.mailSubject"
    ) {
      value = buildWaHref(value);
    }

    if (
      attr === "data-package" ||
      attr === "data-billing" ||
      attr === "data-monthly" ||
      attr === "data-wa-group" ||
      attr === "data-regular" ||
      attr === "data-offer"
    ) {
      el.setAttribute(attr, value);
      // Keep dataset in sync for camelCase consumers (e.g. script.js)
      var dsName = attr.replace(/^data-/, "").replace(/-([a-z])/g, function (_, c) {
        return c.toUpperCase();
      });
      try {
        el.dataset[dsName] = value;
      } catch (_) {}
      return;
    }

    el.setAttribute(attr, value);
  }

  function applyLang(lang) {
    if (!SUPPORTED[lang]) lang = DEFAULT_LANG;
    currentLang = lang;

    document.documentElement.lang = lang === "bn" ? "bn" : "en";

    var title = t("meta.title");
    if (title) document.title = title;

    var metaDesc = document.querySelector('meta[name="description"]');
    if (metaDesc) metaDesc.setAttribute("content", t("meta.description"));

    document.querySelectorAll("[data-i18n]").forEach(function (el) {
      if (el.hasAttribute("data-title-animate")) return;
      var key = el.getAttribute("data-i18n");
      if (!key) return;
      el.textContent = t(key);
    });

    document.querySelectorAll("[data-i18n-html]").forEach(function (el) {
      var key = el.getAttribute("data-i18n-html");
      if (!key) return;
      el.innerHTML = t(key);
    });

    document.querySelectorAll("[data-i18n-attr]").forEach(function (el) {
      var raw = el.getAttribute("data-i18n-attr");
      if (!raw) return;
      raw.split(",").forEach(function (pair) {
        var idx = pair.indexOf(":");
        if (idx === -1) return;
        applyAttrPair(el, pair.slice(0, idx), pair.slice(idx + 1));
      });
    });

    document.querySelectorAll(".lang-switch button[data-lang], button[data-lang]").forEach(function (btn) {
      var isActive = btn.getAttribute("data-lang") === lang;
      btn.classList.toggle("is-active", isActive);
      btn.setAttribute("aria-pressed", isActive ? "true" : "false");
    });

    // Restore footer year after HTML i18n may recreate #year
    var yearEl = document.getElementById("year");
    if (yearEl) {
      yearEl.textContent = String(new Date().getFullYear());
    }

    // Rebuild mailto subject when present
    document.querySelectorAll("[data-mail-subject]").forEach(function (el) {
      el.setAttribute(
        "href",
        "mailto:hello@onetech.com.bd?subject=" +
          encodeURIComponent(t("wa.mailSubject"))
      );
    });

    try {
      localStorage.setItem(STORAGE_KEY, lang);
    } catch (_) {}

    document.documentElement.dispatchEvent(
      new CustomEvent("onepos:langchange", { detail: { lang: lang } })
    );
  }

  function resolveInitialLang() {
    try {
      var params = new URLSearchParams(window.location.search);
      var q = params.get("lang");
      if (q && SUPPORTED[q]) return q;
    } catch (_) {}

    var fromCookie = readLocaleCookie();
    if (fromCookie) return fromCookie;

    try {
      var stored = localStorage.getItem(STORAGE_KEY);
      if (stored && SUPPORTED[stored]) return stored;
    } catch (_) {}

    return DEFAULT_LANG;
  }

  function wireLangButtons() {
    document.querySelectorAll("[data-lang]").forEach(function (btn) {
      btn.addEventListener("click", function () {
        var lang = btn.getAttribute("data-lang");
        if (SUPPORTED[lang]) setLang(lang);
      });
    });
  }

  function init() {
    var lang = resolveInitialLang();
    // Query override already persisted via applyLang → setLang path
    applyLang(lang);
    wireLangButtons();
  }

  window.OnePOS_I18N = {
    t: t,
    applyLang: applyLang,
    getLang: getLang,
    setLang: setLang,
    STRINGS: STRINGS,
  };

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
