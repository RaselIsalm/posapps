<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="OnePOS by ONETECH - a flexible custom POS solution built for any business." />
  <title>OnePOS by ONETECH | Smart POS for Any Business</title>
  <link rel="icon" href="{{ asset('landing/assets/favicon.png') }}?v=8" type="image/png" />
  <link rel="icon" href="{{ asset('landing/assets/favicon-32.png') }}?v=8" type="image/png" sizes="32x32" />
  <link rel="apple-touch-icon" href="{{ asset('landing/assets/apple-touch-icon.png') }}?v=8" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Baloo+Da+2:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('landing/styles.css') }}" />
</head>
<body>
  <div id="top" tabindex="-1"></div>
  <div class="page-atmosphere" aria-hidden="true"></div>
  <div class="page-glow glow-1"></div>
  <div class="page-glow glow-2"></div>
  <div class="page-glow glow-3"></div>

  <header class="site-header">
    <div class="container header-inner">
      <a href="#top" class="brand" data-i18n-attr="aria-label:brand.home" aria-label="OnePOS home">
        <img src="{{ asset('landing/assets/onepos-logo.png') }}?v=8" alt="OnePOS" class="brand-logo" />
      </a>

      <button class="nav-toggle" data-i18n-attr="aria-label:nav.openMenu" aria-label="Open menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>

      <nav class="nav-links" data-i18n-attr="aria-label:nav.primary" aria-label="Primary navigation">
        <a href="#features" data-i18n="nav.features">Features</a>
        <a href="#payments" data-i18n="nav.payments">Payments</a>
        <a href="#businesses" data-i18n="nav.businesses">Businesses</a>
        <a href="#packages" data-i18n="nav.packages">Packages</a>
        <a href="https://onetech.com.bd/contact" target="_blank" rel="noopener" data-i18n="nav.contact">Contact</a>
        <div class="lang-switch" role="group" aria-label="Language">
          <button type="button" data-lang="en" class="is-active" aria-pressed="true">EN</button>
          <button type="button" data-lang="bn" aria-pressed="false">বাং</button>
        </div>
        <a class="btn btn-sm btn-primary" href="https://wa.me/8801994433400?text=Hello%20ONETECH%2C%20I%20want%20to%20know%20more%20about%20OnePOS." data-i18n-attr="href:wa.nav" data-i18n="nav.whatsapp" target="_blank" rel="noopener">WhatsApp Us</a>
      </nav>
    </div>
  </header>

  <main>
    <section class="hero section">
      <div class="container hero-grid">
        <div class="hero-copy reveal hero-animate">
          <div class="eyebrow hero-line" style="--d:0"><span class="dot"></span> <span data-i18n="hero.eyebrow">Custom POS solution by ONETECH</span></div>
          <h1 class="hero-line hero-headline" style="--d:1" data-hero-typewriter>
            <span class="hero-type-title"></span>
            <span class="hero-type-sub"></span>
          </h1>
          <p class="hero-text hero-line" style="--d:2" data-i18n="hero.text">
            A flexible point-of-sale system for retail, restaurants, pharmacies, warehouses and growing chains — configured the way you actually work.
          </p>

          <div class="hero-actions hero-line" style="--d:3">
            <a href="#packages" class="btn btn-primary" data-i18n="hero.ctaPackages">View Packages</a>
            <a href="{{ url('/login') }}" class="btn btn-ghost demo-login-btn" target="_blank" rel="noopener" data-i18n="hero.ctaDemo">Request a Demo</a>
          </div>

          <div class="hero-points hero-line" style="--d:4">
            <span data-i18n="hero.point1" class="hero-point" style="--p:0">Customizable modules</span>
            <span data-i18n="hero.point2" class="hero-point" style="--p:1">Multi-business ready</span>
            <span data-i18n="hero.point3" class="hero-point" style="--p:2">Local ONETECH support</span>
          </div>
        </div>

        <div class="hero-visual reveal delay-1" data-i18n-attr="aria-label:hero.previewLabel" aria-label="OnePOS interface preview">
          <div class="visual-card main-dashboard">
            <div class="window-bar">
              <div class="window-dots"><i></i><i></i><i></i></div>
              <span data-i18n="mock.dashboardTitle">OnePOS Dashboard</span>
            </div>
            <div class="dashboard-body">
              <aside class="mock-sidebar">
                <img src="{{ asset('landing/assets/favicon.png') }}?v=8" alt="" />
                <span class="active"></span><span></span><span></span><span></span><span></span><span></span>
              </aside>
              <div class="mock-content">
                <div class="welcome-strip">
                  <div>
                    <small data-i18n="mock.welcome">Welcome back</small>
                    <b data-i18n="mock.storeOverview">Store overview</b>
                  </div>
                  <div class="period-pills">
                    <span data-i18n="mock.today">Today</span>
                    <span class="on" data-i18n="mock.thisMonth">This Month</span>
                  </div>
                </div>
                <div class="stats-row">
                  <div class="stat-teal"><small data-i18n="mock.totalSale">Total Sale</small><strong>৳ 48,560</strong></div>
                  <div><small data-i18n="mock.totalPurchase">Total Purchase</small><strong>৳ 21,240</strong></div>
                  <div class="stat-due"><small data-i18n="mock.totalDue">Total Due</small><strong>৳ 50</strong></div>
                </div>
                <div class="mock-panels">
                  <div class="chart-panel">
                    <div class="chart-head"><span data-i18n="mock.salesTrend">Sales trend</span><small data-i18n="mock.sevenDays">7 days</small></div>
                    <div class="bars"><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div>
                  </div>
                  <div class="side-panel">
                    <span data-i18n="mock.cashPosition">Cash position</span>
                    <div><i class="dot-paid"></i><b data-i18n="mock.paid">Paid</b><small>৳ 4,570</small></div>
                    <div><i class="dot-due"></i><b data-i18n="mock.due">Due</b><small>৳ 50</small></div>
                    <div><i class="dot-bal"></i><b data-i18n="mock.balance">Balance</b><small>৳ 4,570</small></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="float-card float-sales">
            <span class="float-icon">৳</span>
            <div><small data-i18n="mock.todaysSales">Today's Sales</small><strong>৳ 48,560</strong></div>
          </div>
          <div class="float-card float-stock">
            <span class="float-icon float-icon-teal">✓</span>
            <div><small data-i18n="mock.inventory">Inventory</small><strong data-i18n="mock.stockSynced">Stock synced</strong></div>
          </div>
        </div>
      </div>
    </section>

    <section class="trust-strip">
      <div class="container trust-grid">
        <div class="trust-item">
          <span class="trust-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 12h16"/><path d="M12 4v16"/><circle cx="12" cy="12" r="8"/></svg>
          </span>
          <div>
            <strong data-i18n="trust.flexible">Flexible</strong>
            <span data-i18n="trust.flexibleDesc">Fits your workflow</span>
          </div>
        </div>
        <div class="trust-item">
          <span class="trust-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 18V8l4 3 4-6 4 4 4 9"/><path d="M4 18h16"/></svg>
          </span>
          <div>
            <strong data-i18n="trust.scalable">Scalable</strong>
            <span data-i18n="trust.scalableDesc">Single shop to multi-branch</span>
          </div>
        </div>
        <div class="trust-item">
          <span class="trust-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="4" y="4" width="16" height="16" rx="3"/><path d="M8 12h8M8 8h5M8 16h6"/></svg>
          </span>
          <div>
            <strong data-i18n="trust.complete">Complete</strong>
            <span data-i18n="trust.completeDesc">Sale to reports in one place</span>
          </div>
        </div>
        <div class="trust-item">
          <span class="trust-ico" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21a8 8 0 1 0-7.5-10.5"/><path d="M5 9v3h3"/><path d="M9 13l2.2 2.2L16 10"/></svg>
          </span>
          <div>
            <strong data-i18n="trust.supported">Supported</strong>
            <span data-i18n="trust.supportedDesc">ONETECH local team</span>
          </div>
        </div>
      </div>
    </section>

    <section class="section modules-section" id="features">
      <div class="container">
        <div class="section-head reveal feat-head-animate">
          <div>
            <div class="eyebrow"><span class="dot"></span> <span data-i18n="feat.eyebrow">Core modules</span></div>
            <h2 data-title-animate data-i18n="feat.title">What each section does</h2>
          </div>
        </div>

        <div class="feature-explorer reveal feat-explorer-animate" data-feature-explorer>
          <aside class="feature-nav" data-i18n-attr="aria-label:feat.navLabel" aria-label="Module categories">
            <div class="feature-group">
              <p class="feature-group-label" data-i18n="feat.groupOps">Operations</p>
              <button type="button" class="feature-nav-btn" data-module="dashboard" data-i18n="feat.navDashboard">Dashboard</button>
              <button type="button" class="feature-nav-btn is-active" data-module="sale" data-i18n="feat.navSale">Sale</button>
              <button type="button" class="feature-nav-btn" data-module="purchase" data-i18n="feat.navPurchase">Purchase</button>
              <button type="button" class="feature-nav-btn" data-module="stock" data-i18n="feat.navStock">Stock</button>
              <button type="button" class="feature-nav-btn" data-module="return" data-i18n="feat.navReturn">Return</button>
              <button type="button" class="feature-nav-btn" data-module="transfers" data-i18n="feat.navTransfers">Transfers</button>
            </div>
            <div class="feature-group">
              <p class="feature-group-label" data-i18n="feat.groupCatalog">Catalog &amp; people</p>
              <button type="button" class="feature-nav-btn" data-module="products" data-i18n="feat.navProducts">Products</button>
              <button type="button" class="feature-nav-btn" data-module="peoples" data-i18n="feat.navPeoples">Peoples</button>
              <button type="button" class="feature-nav-btn" data-module="quotations" data-i18n="feat.navQuotations">Quotations</button>
            </div>
            <div class="feature-group">
              <p class="feature-group-label" data-i18n="feat.groupFinance">Finance</p>
              <button type="button" class="feature-nav-btn" data-module="accounts" data-i18n="feat.navAccounts">Accounts</button>
            </div>
            <div class="feature-group">
              <p class="feature-group-label" data-i18n="feat.groupGrowth">Growth &amp; team</p>
              <button type="button" class="feature-nav-btn" data-module="crm" data-i18n="feat.navCrm">CRM</button>
              <button type="button" class="feature-nav-btn" data-module="hrm" data-i18n="feat.navHrm">HRM</button>
              <button type="button" class="feature-nav-btn" data-module="sms" data-i18n="feat.navSms">SMS</button>
            </div>
            <div class="feature-group">
              <p class="feature-group-label" data-i18n="feat.groupSystem">System</p>
              <button type="button" class="feature-nav-btn" data-module="settings" data-i18n="feat.navSettings">Settings</button>
              <button type="button" class="feature-nav-btn" data-module="api" data-i18n="feat.navApi">API Settings</button>
              <button type="button" class="feature-nav-btn" data-module="reports" data-i18n="feat.navReports">Reports</button>
            </div>
          </aside>

          <div class="feature-panels">
            <article class="feature-panel" data-panel="dashboard" hidden>
              <div class="feature-panel-head">
                <span class="feature-badge" data-i18n="feat.badgeOps">Operations</span>
                <h3 data-i18n="feat.dashboardTitle">Dashboard</h3>
                <p data-i18n="feat.dashboardDesc">Your store overview — live numbers, shortcuts and help when you need it.</p>
              </div>
              <div class="sub-grid">
                <div class="sub-card"><strong data-i18n="feat.dashboardCard1">Dashboard</strong><span data-i18n="feat.dashboardCard1Desc">See sales, purchases, dues, profit and cash position at a glance.</span></div>
                <div class="sub-card"><strong data-i18n="feat.dashboardCard2">Help &amp; FAQ</strong><span data-i18n="feat.dashboardCard2Desc">Quick answers and guidance for common OnePOS tasks.</span></div>
              </div>
            </article>

            <article class="feature-panel is-active" data-panel="sale">
              <div class="feature-panel-head">
                <span class="feature-badge" data-i18n="feat.badgeOps">Operations</span>
                <h3 data-i18n="feat.saleTitle">Sale</h3>
                <p data-i18n="feat.saleDesc">Run the counter fast — bill customers, manage offers and handle delivery from one place.</p>
              </div>
              <div class="sub-grid">
                <div class="sub-card"><strong data-i18n="feat.saleQuick">Quick Sale</strong><span data-i18n="feat.saleQuickDesc">Speed checkout for busy counters with barcode and hold support.</span></div>
                <div class="sub-card"><strong data-i18n="feat.saleList">Sale List</strong><span data-i18n="feat.saleListDesc">Search, view and manage every past sales invoice.</span></div>
                <div class="sub-card"><strong data-i18n="feat.saleGift">Gift Card</strong><span data-i18n="feat.saleGiftDesc">Issue, track and redeem store gift cards.</span></div>
                <div class="sub-card"><strong data-i18n="feat.saleCoupons">Coupons</strong><span data-i18n="feat.saleCouponsDesc">Create and control discount codes for promotions.</span></div>
                <div class="sub-card"><strong data-i18n="feat.saleReward">Reward Points</strong><span data-i18n="feat.saleRewardDesc">Run loyalty points so repeat customers earn and redeem.</span></div>
                <div class="sub-card"><strong data-i18n="feat.saleDelivery">Delivery</strong><span data-i18n="feat.saleDeliveryDesc">Manage delivery orders and handoff status.</span></div>
              </div>
            </article>

            <article class="feature-panel" data-panel="purchase" hidden>
              <div class="feature-panel-head">
                <span class="feature-badge" data-i18n="feat.badgeOps">Operations</span>
                <h3 data-i18n="feat.purchaseTitle">Purchase</h3>
                <p data-i18n="feat.purchaseDesc">Record supplier buys and keep purchase history tidy for costing and stock.</p>
              </div>
              <div class="sub-grid">
                <div class="sub-card"><strong data-i18n="feat.purchaseList">Purchase List</strong><span data-i18n="feat.purchaseListDesc">Browse and filter all supplier purchases and costs.</span></div>
                <div class="sub-card"><strong data-i18n="feat.purchaseAdd">Add Purchase</strong><span data-i18n="feat.purchaseAddDesc">Enter a new purchase with items, qty and supplier details.</span></div>
              </div>
            </article>

            <article class="feature-panel" data-panel="stock" hidden>
              <div class="feature-panel-head">
                <span class="feature-badge" data-i18n="feat.badgeOps">Operations</span>
                <h3 data-i18n="feat.stockTitle">Stock</h3>
                <p data-i18n="feat.stockDesc">Know what you have on hand and how stock moved over time.</p>
              </div>
              <div class="sub-grid">
                <div class="sub-card"><strong data-i18n="feat.stockInventory">Inventory List</strong><span data-i18n="feat.stockInventoryDesc">Live quantities, availability and stock value by product.</span></div>
                <div class="sub-card"><strong data-i18n="feat.stockHistory">Stock History</strong><span data-i18n="feat.stockHistoryDesc">Track additions, deductions and adjustments over time.</span></div>
              </div>
            </article>

            <article class="feature-panel" data-panel="return" hidden>
              <div class="feature-panel-head">
                <span class="feature-badge" data-i18n="feat.badgeOps">Operations</span>
                <h3 data-i18n="feat.returnTitle">Return</h3>
                <p data-i18n="feat.returnDesc">Handle product returns cleanly for both customers and suppliers.</p>
              </div>
              <div class="sub-grid">
                <div class="sub-card"><strong data-i18n="feat.returnSale">Sale Return</strong><span data-i18n="feat.returnSaleDesc">Process items customers bring back and update stock.</span></div>
                <div class="sub-card"><strong data-i18n="feat.returnPurchase">Purchase Return</strong><span data-i18n="feat.returnPurchaseDesc">Return goods to suppliers and adjust purchase records.</span></div>
              </div>
            </article>

            <article class="feature-panel" data-panel="transfers" hidden>
              <div class="feature-panel-head">
                <span class="feature-badge" data-i18n="feat.badgeOps">Operations</span>
                <h3 data-i18n="feat.transfersTitle">Transfers</h3>
                <p data-i18n="feat.transfersDesc">Move stock between branches or warehouses without losing track.</p>
              </div>
              <div class="sub-grid">
                <div class="sub-card"><strong data-i18n="feat.transfersList">Transfers</strong><span data-i18n="feat.transfersListDesc">View pending and completed stock movements.</span></div>
                <div class="sub-card"><strong data-i18n="feat.transfersAdd">Add Transfer</strong><span data-i18n="feat.transfersAddDesc">Create a new transfer and update inventory live.</span></div>
              </div>
            </article>

            <article class="feature-panel" data-panel="products" hidden>
              <div class="feature-panel-head">
                <span class="feature-badge" data-i18n="feat.badgeCatalog">Catalog &amp; people</span>
                <h3 data-i18n="feat.productsTitle">Products</h3>
                <p data-i18n="feat.productsDesc">Build and organise your catalogue — items, categories, brands and barcodes.</p>
              </div>
              <div class="sub-grid">
                <div class="sub-card"><strong data-i18n="feat.productsList">Product List</strong><span data-i18n="feat.productsListDesc">Search, filter and manage every product in one place.</span></div>
                <div class="sub-card"><strong data-i18n="feat.productsCreate">Create Product</strong><span data-i18n="feat.productsCreateDesc">Add a new item with price, tax, SKU and stock.</span></div>
                <div class="sub-card"><strong data-i18n="feat.productsBulk">Bulk Product Import</strong><span data-i18n="feat.productsBulkDesc">Upload many products at once via spreadsheet.</span></div>
                <div class="sub-card"><strong data-i18n="feat.productsCategories">Categories</strong><span data-i18n="feat.productsCategoriesDesc">Group products for faster billing and reports.</span></div>
                <div class="sub-card"><strong data-i18n="feat.productsBrands">Brands</strong><span data-i18n="feat.productsBrandsDesc">Manage manufacturer or brand names on items.</span></div>
                <div class="sub-card"><strong data-i18n="feat.productsBarcode">Print Barcode</strong><span data-i18n="feat.productsBarcodeDesc">Generate and print scannable product labels.</span></div>
              </div>
            </article>

            <article class="feature-panel" data-panel="peoples" hidden>
              <div class="feature-panel-head">
                <span class="feature-badge" data-i18n="feat.badgeCatalog">Catalog &amp; people</span>
                <h3 data-i18n="feat.peoplesTitle">Peoples</h3>
                <p data-i18n="feat.peoplesDesc">Keep customers and suppliers organised with groups and full profiles.</p>
              </div>
              <div class="sub-grid">
                <div class="sub-card"><strong data-i18n="feat.peoplesGroups">Customer Groups</strong><span data-i18n="feat.peoplesGroupsDesc">Segment clients (retail, wholesale and more).</span></div>
                <div class="sub-card"><strong data-i18n="feat.peoplesCustomers">Customers</strong><span data-i18n="feat.peoplesCustomersDesc">Profiles, history and contact details for buyers.</span></div>
                <div class="sub-card"><strong data-i18n="feat.peoplesSuppliers">Suppliers</strong><span data-i18n="feat.peoplesSuppliersDesc">Vendor records for purchasing and dues.</span></div>
              </div>
            </article>

            <article class="feature-panel" data-panel="quotations" hidden>
              <div class="feature-panel-head">
                <span class="feature-badge" data-i18n="feat.badgeCatalog">Catalog &amp; people</span>
                <h3 data-i18n="feat.quotationsTitle">Quotations</h3>
                <p data-i18n="feat.quotationsDesc">Send price estimates to customers, then turn accepted quotes into sales.</p>
              </div>
              <div class="sub-grid">
                <div class="sub-card"><strong data-i18n="feat.quotationsAdd">Add Quotations</strong><span data-i18n="feat.quotationsAddDesc">Build and send a new quote with products and pricing.</span></div>
                <div class="sub-card"><strong data-i18n="feat.quotationsList">Quotations List</strong><span data-i18n="feat.quotationsListDesc">Track quote status, follow up and convert to sale.</span></div>
              </div>
            </article>

            <article class="feature-panel" data-panel="accounts" hidden>
              <div class="feature-panel-head">
                <span class="feature-badge" data-i18n="feat.badgeFinance">Finance</span>
                <h3 data-i18n="feat.accountsTitle">Accounts</h3>
                <p data-i18n="feat.accountsDesc">Follow money in and out — accounts, statements, expenses and balances.</p>
              </div>
              <div class="sub-grid">
                <div class="sub-card"><strong data-i18n="feat.accountsView">View Accounts</strong><span data-i18n="feat.accountsViewDesc">Manage cash, bank and other financial accounts.</span></div>
                <div class="sub-card"><strong data-i18n="feat.accountsStatement">Statement</strong><span data-i18n="feat.accountsStatementDesc">Generate account statements for any date range.</span></div>
                <div class="sub-card"><strong data-i18n="feat.accountsBalance">Balance Sheet</strong><span data-i18n="feat.accountsBalanceDesc">See assets, liabilities and overall financial position.</span></div>
                <div class="sub-card"><strong data-i18n="feat.accountsExpenses">View Expenses</strong><span data-i18n="feat.accountsExpensesDesc">Track every business expense in one list.</span></div>
                <div class="sub-card"><strong data-i18n="feat.accountsExpenseCat">Expenses Categories</strong><span data-i18n="feat.accountsExpenseCatDesc">Organize spending by rent, utilities, stock and more.</span></div>
              </div>
            </article>

            <article class="feature-panel" data-panel="crm" hidden>
              <div class="feature-panel-head">
                <span class="feature-badge" data-i18n="feat.badgeGrowth">Growth &amp; team</span>
                <h3 data-i18n="feat.crmTitle">CRM</h3>
                <p data-i18n="feat.crmDesc">Capture leads and follow them through to customers.</p>
              </div>
              <div class="sub-grid">
                <div class="sub-card"><strong data-i18n="feat.crmLeads">Leads</strong><span data-i18n="feat.crmLeadsDesc">Track potential customers in your pipeline.</span></div>
                <div class="sub-card"><strong data-i18n="feat.crmSource">Lead Source</strong><span data-i18n="feat.crmSourceDesc">See where leads come from — ads, referral, walk-in.</span></div>
                <div class="sub-card"><strong data-i18n="feat.crmStatus">Lead Status</strong><span data-i18n="feat.crmStatusDesc">Move leads through stages like new, contacted, won.</span></div>
                <div class="sub-card"><strong data-i18n="feat.crmNotes">Lead Notes</strong><span data-i18n="feat.crmNotesDesc">Save follow-up notes against each lead.</span></div>
              </div>
            </article>

            <article class="feature-panel" data-panel="hrm" hidden>
              <div class="feature-panel-head">
                <span class="feature-badge" data-i18n="feat.badgeGrowth">Growth &amp; team</span>
                <h3 data-i18n="feat.hrmTitle">HRM</h3>
                <p data-i18n="feat.hrmDesc">Manage staff, attendance, departments and payroll basics.</p>
              </div>
              <div class="sub-grid">
                <div class="sub-card"><strong data-i18n="feat.hrmEmployee">Employee</strong><span data-i18n="feat.hrmEmployeeDesc">Staff profiles and employment records.</span></div>
                <div class="sub-card"><strong data-i18n="feat.hrmAttendance">Attendance</strong><span data-i18n="feat.hrmAttendanceDesc">Track who came in and when.</span></div>
                <div class="sub-card"><strong data-i18n="feat.hrmDepartment">Department</strong><span data-i18n="feat.hrmDepartmentDesc">Organise teams by department.</span></div>
                <div class="sub-card"><strong data-i18n="feat.hrmPayrolls">Payrolls</strong><span data-i18n="feat.hrmPayrollsDesc">Salary processing and payroll records.</span></div>
              </div>
            </article>

            <article class="feature-panel" data-panel="sms" hidden>
              <div class="feature-panel-head">
                <span class="feature-badge" data-i18n="feat.badgeGrowth">Growth &amp; team</span>
                <h3 data-i18n="feat.smsTitle">SMS</h3>
                <p data-i18n="feat.smsDesc">Message customers and staff with reusable templates and a full send history.</p>
              </div>
              <div class="sub-grid">
                <div class="sub-card"><strong data-i18n="feat.smsLogs">SMS Logs</strong><span data-i18n="feat.smsLogsDesc">History of sent messages and delivery status.</span></div>
                <div class="sub-card"><strong data-i18n="feat.smsTemplates">SMS Templates</strong><span data-i18n="feat.smsTemplatesDesc">Save reusable texts for invoices, reminders and offers.</span></div>
              </div>
            </article>

            <article class="feature-panel" data-panel="settings" hidden>
              <div class="feature-panel-head">
                <span class="feature-badge" data-i18n="feat.badgeSystem">System</span>
                <h3 data-i18n="feat.settingsTitle">Settings</h3>
                <p data-i18n="feat.settingsDesc">Configure the store, branding, print, users, branches and system security.</p>
              </div>
              <div class="sub-grid">
                <div class="sub-card"><strong data-i18n="feat.settingsGeneral">General Settings</strong><span data-i18n="feat.settingsGeneralDesc">Shop name, contacts, address, logo and timezone.</span></div>
                <div class="sub-card"><strong data-i18n="feat.settingsColor">Color Settings</strong><span data-i18n="feat.settingsColorDesc">Match the app look to your brand colours.</span></div>
                <div class="sub-card"><strong data-i18n="feat.settingsPrint">Print Settings</strong><span data-i18n="feat.settingsPrintDesc">Invoice/receipt templates and printer setup.</span></div>
                <div class="sub-card"><strong data-i18n="feat.settingsTerms">Terms &amp; Conditions</strong><span data-i18n="feat.settingsTermsDesc">Text that appears on customer documents.</span></div>
                <div class="sub-card"><strong data-i18n="feat.settingsMail">Mail Settings</strong><span data-i18n="feat.settingsMailDesc">SMTP setup for invoices, reports and alerts.</span></div>
                <div class="sub-card"><strong data-i18n="feat.settingsBranches">Branches</strong><span data-i18n="feat.settingsBranchesDesc">Manage multiple outlets from one system.</span></div>
                <div class="sub-card"><strong data-i18n="feat.settingsRoles">Manage Roles</strong><span data-i18n="feat.settingsRolesDesc">Define Admin, Cashier, Manager and other permissions.</span></div>
                <div class="sub-card"><strong data-i18n="feat.settingsUsers">Manage Users</strong><span data-i18n="feat.settingsUsersDesc">Add staff accounts and assign roles/branches.</span></div>
                <div class="sub-card"><strong data-i18n="feat.settingsUnits">Units</strong><span data-i18n="feat.settingsUnitsDesc">Set product units like kg, pcs and liter.</span></div>
                <div class="sub-card"><strong data-i18n="feat.settingsCurrencies">Currencies</strong><span data-i18n="feat.settingsCurrenciesDesc">Primary currency and exchange options.</span></div>
                <div class="sub-card"><strong data-i18n="feat.settingsLogs">Activities Logs</strong><span data-i18n="feat.settingsLogsDesc">Audit trail of user actions for security.</span></div>
              </div>
            </article>

            <article class="feature-panel" data-panel="api" hidden>
              <div class="feature-panel-head">
                <span class="feature-badge" data-i18n="feat.badgeSystem">System</span>
                <h3 data-i18n="feat.apiTitle">API Settings</h3>
                <p data-i18n="feat.apiDesc">Connect OnePOS to SMS gateways, couriers and fraud checks.</p>
              </div>
              <div class="sub-grid">
                <div class="sub-card"><strong data-i18n="feat.apiSms">SMS Settings</strong><span data-i18n="feat.apiSmsDesc">Configure SMS gateway for alerts and confirmations.</span></div>
                <div class="sub-card"><strong data-i18n="feat.apiCourier">Courier Settings</strong><span data-i18n="feat.apiCourierDesc">Link courier partners for shipping and tracking.</span></div>
                <div class="sub-card"><strong data-i18n="feat.apiFraud">Fraud Check API</strong><span data-i18n="feat.apiFraudDesc">Flag risky orders with fraud detection tools.</span></div>
              </div>
            </article>

            <article class="feature-panel" data-panel="reports" hidden>
              <div class="feature-panel-head">
                <span class="feature-badge" data-i18n="feat.badgeSystem">System</span>
                <h3 data-i18n="feat.reportsTitle">Reports</h3>
                <p data-i18n="feat.reportsDesc">Understand performance across sales, stock, people and branches.</p>
              </div>
              <div class="sub-grid">
                <div class="sub-card"><strong data-i18n="feat.reportsSummary">Summary Reports</strong><span data-i18n="feat.reportsSummaryDesc">High-level business performance in one view.</span></div>
                <div class="sub-card"><strong data-i18n="feat.reportsSales">Sales Report</strong><span data-i18n="feat.reportsSalesDesc">Revenue, invoices and product movement.</span></div>
                <div class="sub-card"><strong data-i18n="feat.reportsPurchases">Purchases Report</strong><span data-i18n="feat.reportsPurchasesDesc">Supplier buys and purchase spend.</span></div>
                <div class="sub-card"><strong data-i18n="feat.reportsCustomerLedger">Customer Ledger Report</strong><span data-i18n="feat.reportsCustomerLedgerDesc">Per-customer balances, payments and dues.</span></div>
                <div class="sub-card"><strong data-i18n="feat.reportsSupplierLedger">Supplier Ledger Report</strong><span data-i18n="feat.reportsSupplierLedgerDesc">Per-supplier balances and payment history.</span></div>
                <div class="sub-card"><strong data-i18n="feat.reportsInventory">Inventory Report</strong><span data-i18n="feat.reportsInventoryDesc">Stock levels, value and movement.</span></div>
                <div class="sub-card"><strong data-i18n="feat.reportsEmployees">Employees Report</strong><span data-i18n="feat.reportsEmployeesDesc">Staff performance and related HR figures.</span></div>
                <div class="sub-card"><strong data-i18n="feat.reportsBranch">Branch Report</strong><span data-i18n="feat.reportsBranchDesc">Compare results across outlets.</span></div>
              </div>
            </article>
          </div>
        </div>
      </div>
    </section>

    <section class="section payments-section" id="payments">
      <div class="container">
        <div class="payments-layout reveal title-animate-scope">
          <div class="payments-copy">
            <div class="eyebrow"><span class="dot"></span> <span data-i18n="pay.eyebrow">Payment system</span></div>
            <h2 data-title-animate data-i18n="pay.title">Every payment. Crystal clear.</h2>
            <p data-i18n="pay.intro">Built for Bangladesh retail — take payment the way your customers pay, then see paid, due and balances in one place.</p>
            <ul class="payment-highlights">
              <li data-i18n="pay.h1">Partial payments and remaining due</li>
              <li data-i18n="pay.h2">Customer due receive &amp; purchase due</li>
              <li data-i18n="pay.h3">Paid vs unpaid visibility on every bill</li>
              <li data-i18n="pay.h4">Account balance stay in sync with cash flow</li>
            </ul>
          </div>

          <div class="payments-panel">
            <div class="pay-methods" data-i18n-attr="aria-label:pay.methodsLabel" aria-label="Supported payment methods">
              <span data-i18n="pay.cash">Cash</span>
              <span data-i18n="pay.card">Card</span>
              <span data-i18n="pay.mobileBanking">Mobile banking</span>
              <span data-i18n="pay.bankTransfer">Bank transfer</span>
              <span data-i18n="pay.dueCredit">Due / credit</span>
            </div>
            <div class="pay-stats">
              <div class="pay-stat paid">
                <small data-i18n="pay.totalPaid">Total Paid</small>
                <strong>৳ 4,570.75</strong>
              </div>
              <div class="pay-stat due">
                <small data-i18n="pay.totalDue">Total Due</small>
                <strong>৳ 50.00</strong>
              </div>
              <div class="pay-stat purchase">
                <small data-i18n="pay.purchaseDue">Purchase Due</small>
                <strong>৳ 1,551.00</strong>
              </div>
              <div class="pay-stat balance">
                <small data-i18n="pay.accountBalance">Account Balance</small>
                <strong>৳ 4,570.75</strong>
              </div>
            </div>
            <p class="pay-note" data-i18n="pay.note">Cash, card, bKash, Nagad and other mobile wallets — configured for your store.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="section business-section" id="businesses">
      <div class="container business-wrap reveal biz-animate">
        <div class="business-copy">
          <div class="eyebrow light"><span class="dot"></span> <span data-i18n="biz.eyebrow">Made for real businesses</span></div>
          <h2 data-title-animate data-i18n="biz.title">OnePOS can adapt to almost any business model</h2>
          <p data-i18n="biz.text">Instead of forcing your business into a fixed software flow, OnePOS can be configured around your products, staff, branches, reporting and operational needs.</p>
          <a class="text-link" href="https://wa.me/8801994433400?text=Hello%20ONETECH%2C%20I%20want%20to%20check%20whether%20OnePOS%20can%20be%20customized%20for%20my%20business." data-i18n-attr="href:wa.business" data-i18n="biz.link" target="_blank" rel="noopener">Discuss your business setup →</a>
        </div>
        <div class="business-tags">
          <span data-i18n="biz.retail">Retail Shop</span><span data-i18n="biz.restaurant">Restaurant</span><span data-i18n="biz.pharmacy">Pharmacy</span><span data-i18n="biz.superShop">Super Shop</span>
          <span data-i18n="biz.wholesale">Wholesale</span><span data-i18n="biz.fashion">Fashion</span><span data-i18n="biz.electronics">Electronics</span><span data-i18n="biz.serviceCenter">Service Center</span>
          <span data-i18n="biz.warehouse">Warehouse</span><span data-i18n="biz.distributor">Distributor</span><span data-i18n="biz.salon">Salon</span><span data-i18n="biz.custom">Custom Business</span>
        </div>
      </div>
    </section>

    <section class="section" id="packages">
      <div class="container">
        <div class="section-head centered reveal">
          <div>
            <div class="eyebrow"><span class="dot"></span> <span data-i18n="pkg.eyebrow">Pricing packages</span></div>
            <h2 class="line-one" data-i18n="pkg.title">Monthly subscription or one-time purchase</h2>
          </div>
          <p data-i18n="pkg.intro">Pick a plan — open Core Features, Customization and Website details inside each card, then WhatsApp us with that package.</p>
        </div>

        <div class="package-tabs reveal" role="tablist" data-i18n-attr="aria-label:pkg.tabsLabel" aria-label="Package billing type">
          <button type="button" class="package-tab is-active" role="tab" aria-selected="true" data-package-tab="monthly" data-i18n="pkg.tabMonthly">Monthly Subscription</button>
          <button type="button" class="package-tab" role="tab" aria-selected="false" data-package-tab="onetime" data-i18n="pkg.tabOnetime">One-Time Purchase</button>
        </div>

        <div class="pricing-panels">
          <!-- MONTHLY -->
          <div class="pricing-panel is-active" data-package-panel="monthly">
            <div class="pricing-grid">
              <article class="price-card reveal"
                data-package="BASIC POS"
                data-billing="Monthly Subscription"
                data-regular="৳20,000"
                data-offer="৳4,999"
                data-monthly="৳390/month for 1 Branch; Additional Branch ৳300/month"
                data-i18n-attr="data-package:pkg.basicName,data-billing:pkg.billingMonthly,data-monthly:pkg.dataMonthlyMeta">
                <div class="price-top">
                  <span class="package-label" data-i18n="pkg.label01">Package 01</span>
                  <h3 data-i18n="pkg.basicName">BASIC POS</h3>
                  <p data-i18n="pkg.basicDesc">A simple, complete POS solution for small and medium businesses</p>
                </div>
                <div class="price-block">
                  <div class="price-row"><span data-i18n="pkg.regular">Regular</span><s>৳20,000</s></div>
                  <div class="price-row offer"><span data-i18n="pkg.offerOnboarding">Offer Onboarding</span><strong>৳4,999</strong></div>
                  <div class="price-meta">
                    <p data-i18n-html="pkg.metaMonthlyBranch">Monthly: <b>৳390</b>/month — 1 Branch</p>
                    <p data-i18n-html="pkg.metaAdditionalBranch">Additional Branch: <b>৳300</b>/month per Branch</p>
                  </div>
                </div>
                <div class="pkg-drops">
                  <div class="pkg-drop is-open">
                    <button type="button" class="pkg-drop-btn" aria-expanded="true" data-i18n="pkg.dropCore">Core Features</button>
                    <ul class="pkg-drop-body package-features" data-wa-group="Core Features" data-i18n-attr="data-wa-group:pkg.waGroupCore">
                      <li data-i18n="pkg.fDashboard">Dashboard</li>
                      <li data-i18n="pkg.fSalesMgmt">Sales Management</li>
                      <li data-i18n="pkg.fPurchaseMgmt">Purchase Management</li>
                      <li data-i18n="pkg.fStockInvMgmt">Stock / Inventory Management</li>
                      <li data-i18n="pkg.fAccounts">Accounts</li>
                      <li data-i18n="pkg.fQuotations">Quotations</li>
                      <li data-i18n="pkg.fProductMgmt">Product Management</li>
                      <li data-i18n="pkg.fCustomerSupplier">Customer &amp; Supplier / Peoples Management</li>
                      <li data-i18n="pkg.fSalesPurchaseReturn">Sales &amp; Purchase Return</li>
                      <li data-i18n="pkg.fStockTransfer">Stock Transfer</li>
                      <li data-i18n="pkg.fCrm">CRM</li>
                      <li data-i18n="pkg.fHrm">HRM</li>
                      <li data-i18n="pkg.fSmsMgmt">SMS Management</li>
                      <li data-i18n="pkg.fApiSettings">API Settings</li>
                      <li data-i18n="pkg.fReportsAnalytics">Reports &amp; Analytics</li>
                      <li data-i18n="pkg.fGeneralSettings">General Settings</li>
                      <li data-i18n="pkg.fMultiBranch">Multi-Branch Support</li>
                      <li data-i18n="pkg.fLifetimeUpdate">Lifetime Free Update</li>
                      <li data-i18n="pkg.fLiveSupport">Live support</li>
                      <li data-i18n="pkg.fDataBackup">Data Backup</li>
                    </ul>
                  </div>
                  <div class="pkg-drop">
                    <button type="button" class="pkg-drop-btn" aria-expanded="false" data-i18n="pkg.dropCustom">Customization</button>
                    <ul class="pkg-drop-body package-features" data-wa-group="Customization" data-i18n-attr="data-wa-group:pkg.waGroupCustom" hidden>
                      <li data-i18n="pkg.basicCustomNone">Customization: None</li>
                      <li data-i18n="pkg.basicCustomNote">Standard/Core Features only — no custom development outside the existing system</li>
                    </ul>
                  </div>
                </div>
                <button class="btn btn-package package-btn" type="button" data-i18n="pkg.basicCta">WhatsApp — BASIC POS</button>
              </article>

              <article class="price-card featured reveal delay-1"
                data-package="CUSTOM POS"
                data-billing="Monthly Subscription"
                data-regular="৳25,000"
                data-offer="৳6,999+"
                data-monthly="৳390/month for 1 Branch; Additional Branch ৳300/month"
                data-i18n-attr="data-package:pkg.customName,data-billing:pkg.billingMonthly,data-monthly:pkg.dataMonthlyMeta">
                <div class="popular-badge" data-i18n="pkg.mostPopular">Most Popular</div>
                <div class="price-top">
                  <span class="package-label" data-i18n="pkg.label02">Package 02</span>
                  <h3 data-i18n="pkg.customName">CUSTOM POS</h3>
                  <p data-i18n="pkg.customDesc">For businesses that need the POS customized to their own workflow</p>
                </div>
                <div class="price-block">
                  <div class="price-row"><span data-i18n="pkg.regular">Regular</span><s>৳25,000</s></div>
                  <div class="price-row offer"><span data-i18n="pkg.offerOnboarding">Offer Onboarding</span><strong>৳6,999+</strong></div>
                  <div class="price-meta">
                    <p data-i18n-html="pkg.metaMonthlyBranch">Monthly: <b>৳390</b>/month — 1 Branch</p>
                    <p data-i18n-html="pkg.metaAdditionalBranch">Additional Branch: <b>৳300</b>/month per Branch</p>
                  </div>
                </div>
                <div class="pkg-drops">
                  <div class="pkg-drop is-open">
                    <button type="button" class="pkg-drop-btn" aria-expanded="true" data-i18n="pkg.dropCore">Core Features</button>
                    <ul class="pkg-drop-body package-features" data-wa-group="Core Features" data-i18n-attr="data-wa-group:pkg.waGroupCore">
                      <li data-i18n="pkg.fDashboard">Dashboard</li>
                      <li data-i18n="pkg.fSalesMgmt">Sales Management</li>
                      <li data-i18n="pkg.fPurchaseMgmt">Purchase Management</li>
                      <li data-i18n="pkg.fStockInvMgmt">Stock / Inventory Management</li>
                      <li data-i18n="pkg.fAccounts">Accounts</li>
                      <li data-i18n="pkg.fQuotations">Quotations</li>
                      <li data-i18n="pkg.fProductMgmt">Product Management</li>
                      <li data-i18n="pkg.fCustomerSupplier">Customer &amp; Supplier / Peoples Management</li>
                      <li data-i18n="pkg.fSalesPurchaseReturn">Sales &amp; Purchase Return</li>
                      <li data-i18n="pkg.fStockTransfer">Stock Transfer</li>
                      <li data-i18n="pkg.fCrm">CRM</li>
                      <li data-i18n="pkg.fHrm">HRM</li>
                      <li data-i18n="pkg.fSmsMgmt">SMS Management</li>
                      <li data-i18n="pkg.fApiSettings">API Settings</li>
                      <li data-i18n="pkg.fReportsAnalytics">Reports &amp; Analytics</li>
                      <li data-i18n="pkg.fGeneralSettings">General Settings</li>
                      <li data-i18n="pkg.fMultiBranch">Multi-Branch Support</li>
                      <li data-i18n="pkg.fLifetimeUpdate">Lifetime Free Update</li>
                      <li data-i18n="pkg.fLiveSupport">Live support</li>
                      <li data-i18n="pkg.fDataBackup">Data Backup</li>
                    </ul>
                  </div>
                  <div class="pkg-drop">
                    <button type="button" class="pkg-drop-btn" aria-expanded="false" data-i18n="pkg.dropCustomBenefit">Customization benefits</button>
                    <ul class="pkg-drop-body package-features" data-wa-group="Customization" data-i18n-attr="data-wa-group:pkg.waGroupCustom" hidden>
                      <li data-i18n="pkg.customC1">Customize the POS according to your business</li>
                      <li data-i18n="pkg.customC2">Small changes to existing features are possible</li>
                      <li data-i18n="pkg.customC3">Build custom workflows as needed</li>
                      <li data-i18n="pkg.customC4">Additional module development available</li>
                      <li data-i18n="pkg.customC5">Custom Module Development: from ৳5,000 / Module</li>
                      <li data-i18n="pkg.customC6">Pricing for minor customizations is set after discussing requirements</li>
                    </ul>
                  </div>
                </div>
                <button class="btn btn-primary package-btn" type="button" data-i18n="pkg.customCta">WhatsApp — CUSTOM POS</button>
              </article>

              <article class="price-card reveal delay-2"
                data-package="POS + BUSINESS WEBSITE"
                data-billing="Monthly Subscription"
                data-regular="৳35,000"
                data-offer="৳9,999+"
                data-monthly="৳390/month for 1 Branch; Additional Branch ৳300/month"
                data-i18n-attr="data-package:pkg.webPackageAttr,data-billing:pkg.billingMonthly,data-monthly:pkg.dataMonthlyMeta">
                <div class="price-top">
                  <span class="package-label" data-i18n="pkg.label03">Package 03</span>
                  <h3 data-i18n="pkg.webName">POS + WEBSITE</h3>
                  <p data-i18n="pkg.webDesc">POS software and a business website together as a complete digital solution</p>
                </div>
                <div class="price-block">
                  <div class="price-row"><span data-i18n="pkg.regular">Regular</span><s>৳35,000</s></div>
                  <div class="price-row offer"><span data-i18n="pkg.offerOnboarding">Offer Onboarding</span><strong>৳9,999+</strong></div>
                  <div class="price-meta">
                    <p data-i18n-html="pkg.metaMonthlyBranch">Monthly: <b>৳390</b>/month — 1 Branch</p>
                    <p data-i18n-html="pkg.metaAdditionalBranch">Additional Branch: <b>৳300</b>/month per Branch</p>
                  </div>
                </div>
                <div class="pkg-drops">
                  <div class="pkg-drop is-open">
                    <button type="button" class="pkg-drop-btn" aria-expanded="true" data-i18n="pkg.dropCorePos">POS Core Features</button>
                    <ul class="pkg-drop-body package-features" data-wa-group="Core Features" data-i18n-attr="data-wa-group:pkg.waGroupCore">
                      <li data-i18n="pkg.fDashboard">Dashboard</li>
                      <li data-i18n="pkg.fSalesMgmt">Sales Management</li>
                      <li data-i18n="pkg.fPurchaseMgmt">Purchase Management</li>
                      <li data-i18n="pkg.fStockInvMgmt">Stock / Inventory Management</li>
                      <li data-i18n="pkg.fAccounts">Accounts</li>
                      <li data-i18n="pkg.fQuotations">Quotations</li>
                      <li data-i18n="pkg.fProductMgmt">Product Management</li>
                      <li data-i18n="pkg.fCustomerSupplier">Customer &amp; Supplier / Peoples Management</li>
                      <li data-i18n="pkg.fSalesPurchaseReturn">Sales &amp; Purchase Return</li>
                      <li data-i18n="pkg.fStockTransfer">Stock Transfer</li>
                      <li data-i18n="pkg.fCrm">CRM</li>
                      <li data-i18n="pkg.fHrm">HRM</li>
                      <li data-i18n="pkg.fSmsMgmt">SMS Management</li>
                      <li data-i18n="pkg.fApiSettings">API Settings</li>
                      <li data-i18n="pkg.fReportsAnalytics">Reports &amp; Analytics</li>
                      <li data-i18n="pkg.fGeneralSettings">General Settings</li>
                      <li data-i18n="pkg.fMultiBranch">Multi-Branch Support</li>
                      <li data-i18n="pkg.fLifetimeUpdate">Lifetime Free Update</li>
                      <li data-i18n="pkg.fLiveSupport">Live support</li>
                      <li data-i18n="pkg.fDataBackup">Data Backup</li>
                    </ul>
                  </div>
                  <div class="pkg-drop">
                    <button type="button" class="pkg-drop-btn" aria-expanded="false" data-i18n="pkg.dropWebsite">Website benefits</button>
                    <ul class="pkg-drop-body package-features" data-wa-group="Website" data-i18n-attr="data-wa-group:pkg.waGroupWebsite" hidden>
                      <li data-i18n="pkg.webW1">Professional Business Website</li>
                      <li data-i18n="pkg.webW2">Mobile Responsive Design</li>
                      <li data-i18n="pkg.webW3">Business Information &amp; Services</li>
                      <li data-i18n="pkg.webW4">Product / Service Showcase</li>
                      <li data-i18n="pkg.webW5">Contact Information</li>
                      <li data-i18n="pkg.webW6">WhatsApp / Contact Integration</li>
                      <li data-i18n="pkg.webW7">Integration with POS as needed</li>
                    </ul>
                  </div>
                  <div class="pkg-drop">
                    <button type="button" class="pkg-drop-btn" aria-expanded="false" data-i18n="pkg.dropCustomBenefit">Customization benefits</button>
                    <ul class="pkg-drop-body package-features" data-wa-group="Customization" data-i18n-attr="data-wa-group:pkg.waGroupCustom" hidden>
                      <li data-i18n="pkg.webC1">Full POS customization</li>
                      <li data-i18n="pkg.webC2">Website customization</li>
                      <li data-i18n="pkg.webC3">Development aligned to your business workflow</li>
                      <li data-i18n="pkg.webC4">Additional module development</li>
                      <li data-i18n="pkg.webC5">Custom Module Development: from ৳5,000 / Module</li>
                      <li data-i18n="pkg.webC6">Minor customization pricing subject to discussion</li>
                    </ul>
                  </div>
                </div>
                <button class="btn btn-package package-btn" type="button" data-i18n="pkg.webCta">WhatsApp — POS + Website</button>
              </article>
            </div>
          </div>

          <!-- ONE-TIME -->
          <div class="pricing-panel" data-package-panel="onetime" hidden>
            <div class="pricing-grid">
              <article class="price-card reveal"
                data-package="BASIC POS - ONE TIME"
                data-billing="One-Time Purchase"
                data-regular="৳60,000"
                data-offer="৳40,000"
                data-monthly="Annual Renewal Fee: Applicable"
                data-i18n-attr="data-package:pkg.basicOtPackageAttr,data-billing:pkg.billingOnetime,data-monthly:pkg.dataAnnualMeta">
                <div class="price-top">
                  <span class="package-label" data-i18n="pkg.label01">Package 01</span>
                  <h3 data-i18n="pkg.basicOtName">BASIC POS — One Time</h3>
                  <p data-i18n="pkg.basicOtDesc">Pay once for OnePOS — Standard/Core version</p>
                </div>
                <div class="price-block">
                  <div class="price-row"><span data-i18n="pkg.regular">Regular</span><s>৳60,000</s></div>
                  <div class="price-row offer"><span data-i18n="pkg.offerPrice">Offer Price</span><strong>৳40,000</strong></div>
                  <div class="price-meta"><p data-i18n-html="pkg.metaAnnualRenewal">Annual Renewal Fee: <b>Applicable</b></p></div>
                </div>
                <div class="pkg-drops">
                  <div class="pkg-drop is-open">
                    <button type="button" class="pkg-drop-btn" aria-expanded="true" data-i18n="pkg.dropIncluded">Included Features</button>
                    <ul class="pkg-drop-body package-features" data-wa-group="Included Features" data-i18n-attr="data-wa-group:pkg.waGroupIncluded">
                      <li data-i18n="pkg.fDashboard">Dashboard</li>
                      <li data-i18n="pkg.fSales">Sales</li>
                      <li data-i18n="pkg.fPurchase">Purchase</li>
                      <li data-i18n="pkg.fStockInv">Stock / Inventory</li>
                      <li data-i18n="pkg.fAccounts">Accounts</li>
                      <li data-i18n="pkg.fQuotations">Quotations</li>
                      <li data-i18n="pkg.fProducts">Products</li>
                      <li data-i18n="pkg.fCustomerSupplierShort">Customer &amp; Supplier Management</li>
                      <li data-i18n="pkg.fReturn">Return</li>
                      <li data-i18n="pkg.fStockTransfer">Stock Transfer</li>
                      <li data-i18n="pkg.fCrm">CRM</li>
                      <li data-i18n="pkg.fHrm">HRM</li>
                      <li data-i18n="pkg.fSms">SMS</li>
                      <li data-i18n="pkg.fApiSettings">API Settings</li>
                      <li data-i18n="pkg.fReports">Reports</li>
                      <li data-i18n="pkg.fSettings">Settings</li>
                      <li data-i18n="pkg.fMultiBranchReady">Multi-Branch Ready</li>
                      <li data-i18n="pkg.fLifetimeUpdate">Lifetime Free Update</li>
                      <li data-i18n="pkg.fLiveSupport">Live support</li>
                      <li data-i18n="pkg.fDataBackup">Data Backup</li>
                    </ul>
                  </div>
                  <div class="pkg-drop">
                    <button type="button" class="pkg-drop-btn" aria-expanded="false" data-i18n="pkg.dropCustom">Customization</button>
                    <ul class="pkg-drop-body package-features" data-wa-group="Customization" data-i18n-attr="data-wa-group:pkg.waGroupCustom" hidden>
                      <li data-i18n="pkg.basicOtC1">Standard/Core Version only</li>
                      <li data-i18n="pkg.basicOtC2">Annual Renewal Fee: Applicable</li>
                    </ul>
                  </div>
                </div>
                <button class="btn btn-package package-btn" type="button" data-i18n="pkg.basicOtCta">WhatsApp — BASIC One Time</button>
              </article>

              <article class="price-card featured reveal delay-1"
                data-package="CUSTOM POS - ONE TIME"
                data-billing="One-Time Purchase"
                data-regular="৳80,000"
                data-offer="৳60,000"
                data-monthly="Annual Renewal Fee: Applicable"
                data-i18n-attr="data-package:pkg.customOtPackageAttr,data-billing:pkg.billingOnetime,data-monthly:pkg.dataAnnualMeta">
                <div class="popular-badge" data-i18n="pkg.mostPopular">Most Popular</div>
                <div class="price-top">
                  <span class="package-label" data-i18n="pkg.label02">Package 02</span>
                  <h3 data-i18n="pkg.customOtName">CUSTOM POS — One Time</h3>
                  <p data-i18n="pkg.customOtDesc">One-time OnePOS purchase with full customization</p>
                </div>
                <div class="price-block">
                  <div class="price-row"><span data-i18n="pkg.regular">Regular</span><s>৳80,000</s></div>
                  <div class="price-row offer"><span data-i18n="pkg.offerPrice">Offer Price</span><strong>৳60,000</strong></div>
                  <div class="price-meta"><p data-i18n-html="pkg.metaAnnualRenewal">Annual Renewal Fee: <b>Applicable</b></p></div>
                </div>
                <div class="pkg-drops">
                  <div class="pkg-drop is-open">
                    <button type="button" class="pkg-drop-btn" aria-expanded="true" data-i18n="pkg.dropIncluded">Included Features</button>
                    <ul class="pkg-drop-body package-features" data-wa-group="Included Features" data-i18n-attr="data-wa-group:pkg.waGroupIncluded">
                      <li data-i18n="pkg.fDashboard">Dashboard</li>
                      <li data-i18n="pkg.fSales">Sales</li>
                      <li data-i18n="pkg.fPurchase">Purchase</li>
                      <li data-i18n="pkg.fStockInv">Stock / Inventory</li>
                      <li data-i18n="pkg.fAccounts">Accounts</li>
                      <li data-i18n="pkg.fQuotations">Quotations</li>
                      <li data-i18n="pkg.fProducts">Products</li>
                      <li data-i18n="pkg.fCustomerSupplierShort">Customer &amp; Supplier Management</li>
                      <li data-i18n="pkg.fReturn">Return</li>
                      <li data-i18n="pkg.fStockTransfer">Stock Transfer</li>
                      <li data-i18n="pkg.fCrm">CRM</li>
                      <li data-i18n="pkg.fHrm">HRM</li>
                      <li data-i18n="pkg.fSms">SMS</li>
                      <li data-i18n="pkg.fApiSettings">API Settings</li>
                      <li data-i18n="pkg.fReportsAnalytics">Reports &amp; Analytics</li>
                      <li data-i18n="pkg.fSettings">Settings</li>
                      <li data-i18n="pkg.fMultiBranch">Multi-Branch Support</li>
                      <li data-i18n="pkg.fLifetimeUpdate">Lifetime Free Update</li>
                      <li data-i18n="pkg.fLiveSupport">Live support</li>
                      <li data-i18n="pkg.fDataBackup">Data Backup</li>
                    </ul>
                  </div>
                  <div class="pkg-drop">
                    <button type="button" class="pkg-drop-btn" aria-expanded="false" data-i18n="pkg.dropCustom">Customization</button>
                    <ul class="pkg-drop-body package-features" data-wa-group="Customization" data-i18n-attr="data-wa-group:pkg.waGroupCustom" hidden>
                      <li data-i18n="pkg.customOtC1">Full customization for your business</li>
                      <li data-i18n="pkg.customOtC2">Custom Workflow</li>
                      <li data-i18n="pkg.customOtC3">Feature Modification</li>
                      <li data-i18n="pkg.customOtC4">Additional Module Development</li>
                      <li data-i18n="pkg.customOtC5">Custom Module: from ৳5,000 / Module</li>
                      <li data-i18n="pkg.customOtC6">Minor customization charges subject to discussion</li>
                      <li data-i18n="pkg.customOtC7">Annual Renewal Fee: Applicable</li>
                    </ul>
                  </div>
                </div>
                <button class="btn btn-primary package-btn" type="button" data-i18n="pkg.customOtCta">WhatsApp — CUSTOM One Time</button>
              </article>

              <article class="price-card reveal delay-2"
                data-package="POS + WEBSITE - ONE TIME"
                data-billing="One-Time Purchase"
                data-regular="৳100,000"
                data-offer="৳80,000"
                data-monthly="Annual Renewal Fee: Applicable"
                data-i18n-attr="data-package:pkg.webOtPackageAttr,data-billing:pkg.billingOnetime,data-monthly:pkg.dataAnnualMeta">
                <div class="price-top">
                  <span class="package-label" data-i18n="pkg.label03">Package 03</span>
                  <h3 data-i18n="pkg.webOtName">POS + WEBSITE — One Time</h3>
                  <p data-i18n="pkg.webOtDesc">Complete OnePOS + Professional Business Website</p>
                </div>
                <div class="price-block">
                  <div class="price-row"><span data-i18n="pkg.regular">Regular</span><s>৳100,000</s></div>
                  <div class="price-row offer"><span data-i18n="pkg.offerPrice">Offer Price</span><strong>৳80,000</strong></div>
                  <div class="price-meta"><p data-i18n-html="pkg.metaAnnualRenewal">Annual Renewal Fee: <b>Applicable</b></p></div>
                </div>
                <div class="pkg-drops">
                  <div class="pkg-drop is-open">
                    <button type="button" class="pkg-drop-btn" aria-expanded="true" data-i18n="pkg.dropIncluded">Included Features</button>
                    <ul class="pkg-drop-body package-features" data-wa-group="Included Features" data-i18n-attr="data-wa-group:pkg.waGroupIncluded">
                      <li data-i18n="pkg.fCompleteSoftware">Complete OnePOS Software</li>
                      <li data-i18n="pkg.fDashboard">Dashboard</li>
                      <li data-i18n="pkg.fSales">Sales</li>
                      <li data-i18n="pkg.fPurchase">Purchase</li>
                      <li data-i18n="pkg.fStockInv">Stock / Inventory</li>
                      <li data-i18n="pkg.fAccounts">Accounts</li>
                      <li data-i18n="pkg.fQuotations">Quotations</li>
                      <li data-i18n="pkg.fProducts">Products</li>
                      <li data-i18n="pkg.fCustomerSupplierShort">Customer &amp; Supplier Management</li>
                      <li data-i18n="pkg.fReturn">Return</li>
                      <li data-i18n="pkg.fTransfers">Transfers</li>
                      <li data-i18n="pkg.fCrm">CRM</li>
                      <li data-i18n="pkg.fHrm">HRM</li>
                      <li data-i18n="pkg.fSms">SMS</li>
                      <li data-i18n="pkg.fApiSettings">API Settings</li>
                      <li data-i18n="pkg.fReportsAnalytics">Reports &amp; Analytics</li>
                      <li data-i18n="pkg.fMultiBranch">Multi-Branch Support</li>
                      <li data-i18n="pkg.fProfWebsite">Professional Business Website</li>
                      <li data-i18n="pkg.fMobileResponsive">Mobile Responsive Website</li>
                      <li data-i18n="pkg.fShowcase">Product / Service Showcase</li>
                      <li data-i18n="pkg.fContactWa">Contact &amp; WhatsApp Integration</li>
                      <li data-i18n="pkg.fPosWebsiteInt">POS + Website Integration</li>
                      <li data-i18n="pkg.fLifetimeUpdate">Lifetime Free Update</li>
                      <li data-i18n="pkg.fLiveSupport">Live support</li>
                      <li data-i18n="pkg.fDataBackup">Data Backup</li>
                    </ul>
                  </div>
                  <div class="pkg-drop">
                    <button type="button" class="pkg-drop-btn" aria-expanded="false" data-i18n="pkg.dropWebsite">Website benefits</button>
                    <ul class="pkg-drop-body package-features" data-wa-group="Website" data-i18n-attr="data-wa-group:pkg.waGroupWebsite" hidden>
                      <li data-i18n="pkg.webOtW1">Professional Business Website</li>
                      <li data-i18n="pkg.webOtW2">Mobile Responsive Website</li>
                      <li data-i18n="pkg.webOtW3">Product / Service Showcase</li>
                      <li data-i18n="pkg.webOtW4">Contact &amp; WhatsApp Integration</li>
                      <li data-i18n="pkg.webOtW5">POS + Website Integration</li>
                    </ul>
                  </div>
                  <div class="pkg-drop">
                    <button type="button" class="pkg-drop-btn" aria-expanded="false" data-i18n="pkg.dropCustom">Customization</button>
                    <ul class="pkg-drop-body package-features" data-wa-group="Customization" data-i18n-attr="data-wa-group:pkg.waGroupCustom" hidden>
                      <li data-i18n="pkg.webOtC1">POS Full Customization</li>
                      <li data-i18n="pkg.webOtC2">Website Customization</li>
                      <li data-i18n="pkg.webOtC3">Custom Business Workflow</li>
                      <li data-i18n="pkg.webOtC4">Additional Feature Development</li>
                      <li data-i18n="pkg.webOtC5">Custom Module: from ৳5,000 / Module</li>
                      <li data-i18n="pkg.webOtC6">Minor customization charges subject to discussion</li>
                      <li data-i18n="pkg.webOtC7">Annual Renewal Fee: Applicable</li>
                    </ul>
                  </div>
                </div>
                <button class="btn btn-package package-btn" type="button" data-i18n="pkg.webOtCta">WhatsApp — POS + Website One Time</button>
              </article>
            </div>
          </div>
        </div>

        <p class="package-note reveal"><span data-i18n="pkg.noteBefore">Need help choosing?</span> <a href="https://wa.me/8801994433400?text=Hello%20ONETECH%2C%20I%20need%20help%20choosing%20a%20OnePOS%20package." data-i18n-attr="href:wa.packages" data-i18n="pkg.noteLink" target="_blank" rel="noopener">Ask ONETECH on WhatsApp.</a></p>
      </div>
    </section>

    <section class="section process-section">
      <div class="container">
        <div class="section-head reveal process-head-animate">
          <div>
            <div class="eyebrow"><span class="dot"></span> <span data-i18n="process.eyebrow">How it works</span></div>
            <h2 data-title-animate data-i18n="process.title">From discussion to go-live</h2>
          </div>
          <p data-i18n="process.intro">We understand your operation first, then configure OnePOS accordingly.</p>
        </div>
        <div class="steps-grid">
          <article class="step reveal"><b>01</b><h3 data-i18n="process.s1Title">Requirement</h3><p data-i18n="process.s1Text">Tell us how your business currently sells, manages stock and reports.</p></article>
          <article class="step reveal delay-1"><b>02</b><h3 data-i18n="process.s2Title">Setup</h3><p data-i18n="process.s2Text">We configure modules, roles, products, invoices and workflow.</p></article>
          <article class="step reveal delay-2"><b>03</b><h3 data-i18n="process.s3Title">Training</h3><p data-i18n="process.s3Text">Your team learns the system with practical business scenarios.</p></article>
          <article class="step reveal"><b>04</b><h3 data-i18n="process.s4Title">Go Live</h3><p data-i18n="process.s4Text">Start using OnePOS with ONETECH support when you need it.</p></article>
        </div>
      </div>
    </section>

    <section class="section cta-section" id="contact">
      <div class="container">
        <div class="cta-box reveal">
          <div>
            <div class="eyebrow light"><span class="dot"></span> <span data-i18n="contact.eyebrow">Ready to upgrade your business?</span></div>
            <h2 data-i18n="contact.title">Let’s build the right OnePOS setup for you.</h2>
            <p data-i18n="contact.text">Send us your business type and requirements. Our team will help you choose the right package or prepare a custom solution.</p>
          </div>
          <div class="cta-actions">
            <a href="https://wa.me/8801994433400?text=Hello%20ONETECH%2C%20I%20want%20to%20get%20started%20with%20OnePOS." data-i18n-attr="href:wa.cta" data-i18n="contact.wa" target="_blank" rel="noopener" class="btn btn-white">Chat on WhatsApp</a>
            <a href="mailto:hello@onetech.com.bd?subject=OnePOS%20Enquiry" class="btn btn-outline-white" data-i18n="contact.email" data-mail-subject>Email Us</a>
          </div>
        </div>

        <div class="contact-grid reveal">
          <div class="contact-brand">
            <div class="footer-brand">
              <img src="{{ asset('landing/assets/onepos-logo.png') }}?v=8" alt="OnePOS" class="brand-logo" />
            </div>
            <p data-i18n="contact.brandText">Custom POS software designed and developed by ONETECH for businesses that need flexibility, visibility and control.</p>
          </div>
          <div class="contact-item">
            <small data-i18n="contact.labelEmail">Email</small>
            <a href="mailto:hello@onetech.com.bd">hello@onetech.com.bd</a>
          </div>
          <div class="contact-item">
            <small data-i18n="contact.labelPhone">Phone / WhatsApp</small>
            <a href="https://wa.me/8801994433400" target="_blank" rel="noopener">01994433400</a>
          </div>
          <div class="contact-item">
            <small data-i18n="contact.labelOffice">Office</small>
            <address data-i18n-html="contact.address">Shop No 11, Sobuj Neel Tower,<br>Shah Kabir Mazar Road, Uttara,<br>Dhaka 1230</address>
          </div>
        </div>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="container footer-inner">
      <p data-i18n-html="footer.copy">© <span id="year"></span> <a href="https://onetech.com.bd/" target="_blank" rel="noopener">ONETECH</a>, all rights reserved.</p>
    </div>
  </footer>

  <button type="button" class="floating-top" id="floatingTop" data-i18n-attr="aria-label:footer.backTop" aria-label="Back to top" hidden>
    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
      <path fill="currentColor" d="M12 5.5 5.8 11.7a1 1 0 0 0 1.4 1.4L11 9.3V19a1 1 0 1 0 2 0V9.3l3.8 3.8a1 1 0 0 0 1.4-1.4L12 5.5z"/>
    </svg>
  </button>

  <a class="floating-whatsapp" href="https://wa.me/8801994433400?text=Hello%20ONETECH%2C%20I%20want%20to%20know%20more%20about%20OnePOS." data-i18n-attr="aria-label:demo.floatWa,href:wa.float" target="_blank" rel="noopener" aria-label="Chat on WhatsApp">
    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
      <path fill="currentColor" d="M19.05 4.91A9.82 9.82 0 0 0 12.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.91-7.01zm-7.01 15.24h-.01a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.22 8.22 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.25-8.24 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.83c.02 4.54-3.7 8.23-8.24 8.23zm4.52-6.16c-.25-.12-1.47-.72-1.7-.81-.22-.08-.39-.12-.55.13-.17.25-.64.8-.78.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-2-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.12-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.41-.55-.42h-.47c-.17 0-.43.06-.66.31-.22.25-.86.85-.86 2.07s.89 2.4 1.01 2.56c.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.07-.1-.25-.17-.5-.29z"/>
    </svg>
  </a>

  <div class="demo-modal" id="demoModal" hidden>
    <div class="demo-modal-backdrop" data-demo-close></div>
    <div class="demo-modal-card" role="dialog" aria-modal="true" aria-labelledby="demoModalTitle">
      <button type="button" class="demo-modal-x" data-demo-close data-i18n-attr="aria-label:demo.close" aria-label="Close">×</button>
      <p class="eyebrow"><span class="dot"></span> <span data-i18n="demo.eyebrow">Live demo</span></p>
      <h3 id="demoModalTitle" data-i18n="demo.title">Sign in with these credentials</h3>
      <p class="demo-modal-text" data-i18n="demo.text">POS login page does not auto-fill from the URL yet. Copy below, then open the demo.</p>
      <label class="demo-field">
        <span data-i18n="demo.email">Email</span>
        <div class="demo-field-row">
          <input type="text" id="demoEmail" readonly value="superadmin@gmail.com" />
          <button type="button" class="btn btn-ghost demo-copy" data-copy="demoEmail" data-i18n="js.copy">Copy</button>
        </div>
      </label>
      <label class="demo-field">
        <span data-i18n="demo.password">Password</span>
        <div class="demo-field-row">
          <input type="text" id="demoPassword" readonly value="12345678" />
          <button type="button" class="btn btn-ghost demo-copy" data-copy="demoPassword" data-i18n="js.copy">Copy</button>
        </div>
      </label>
      <div class="demo-modal-actions">
        <a href="{{ url('/login') }}" class="btn btn-primary" id="demoOpenLogin" target="_blank" rel="noopener" data-i18n="demo.openLogin">Open Demo Login</a>
      </div>
    </div>
  </div>

  <script>window.ONEPOS_LOGIN_URL = @json(url('/login'));</script>
  <script src="{{ asset('landing/i18n.js') }}"></script>
  <script src="{{ asset('landing/script.js') }}"></script>
</body>
</html>