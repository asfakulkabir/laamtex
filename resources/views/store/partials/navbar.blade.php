@php
    $catColors = ['#ee5f73', '#fb56c1', '#f2c210', '#f26a10', '#0db7af', '#2874f0', '#2bb673', '#ff6b81'];

    $navCats = [];
    foreach ($parentCats as $i => $cat) {
        $navCats[] = [
            'id' => $cat->id,
            'name' => $cat->name,
            'slug' => $cat->slug,
            'color' => $catColors[$i % count($catColors)],
            'children' => $cat->children->map(fn ($c) => ['name' => $c->name, 'slug' => $c->slug])->all(),
        ];
    }
    if (empty($navCats)) {
        $navCats = [
            ['id' => 0, 'name' => 'Men', 'slug' => null, 'color' => '#ee5f73', 'children' => []],
            ['id' => 1, 'name' => 'Women', 'slug' => null, 'color' => '#fb56c1', 'children' => []],
            ['id' => 2, 'name' => 'Kids', 'slug' => null, 'color' => '#f2c210', 'children' => []],
            ['id' => 3, 'name' => 'Teens', 'slug' => null, 'color' => '#f26a10', 'children' => []],
            ['id' => 4, 'name' => 'Sports', 'slug' => null, 'color' => '#0db7af', 'children' => []],
        ];
    }

    $navCatUrl = fn ($slug) => $slug ? route('shop', ['category' => $slug]) : route('shop');
    $waLink = $whatsapp ? 'https://wa.me/' . preg_replace('/[^0-9]/', '', $whatsapp) : '#';

    $currentRoute = request()->route() ? request()->route()->getName() : '';
    $isHome = in_array($currentRoute, ['home']);
    $isShop = in_array($currentRoute, ['shop', 'categories', 'product.detail']);
    $isCart = in_array($currentRoute, ['cart', 'checkout', 'order.success']);
@endphp

<!-- Mobile Top Nav -->
<header class="mobile-top-nav">
    <div class="top-nav-left">
        <button class="top-nav-btn menu-btn" id="openMobileMenu" type="button" aria-label="Open menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="4" x2="20" y1="12" y2="12"></line><line x1="4" x2="20" y1="6" y2="6"></line><line x1="4" x2="20" y1="18" y2="18"></line></svg>
        </button>
    </div>
    <div class="top-nav-center">
        <a href="{{ route('home') }}" class="top-nav-logo">
            <img src="{{ site_logo() }}" alt="{{ site_name() }}">
        </a>
    </div>
    <div class="top-nav-right">
        <button class="top-nav-btn search-btn" id="openSearchModal" type="button" aria-label="Search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
        </button>
    </div>
</header>

<!-- Mobile Search Modal -->
<div class="mobile-search-modal" id="searchModal">
    <div class="search-modal-header">
        <button class="search-modal-back" id="closeSearchModal" type="button" aria-label="Close search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg>
        </button>
        <form class="search-input-wrapper" action="{{ route('shop') }}" method="get" style="display:flex; flex:1;">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
            <input type="search" id="mobileSearchInput" name="search" placeholder="Search products..." autocomplete="off">
            <button class="search-clear-btn" id="clearSearch" type="button" aria-label="Clear search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
            </button>
        </form>
    </div>
    <div class="search-suggestions">
        <div class="search-section">
            <div class="search-section-header"><span>Popular Searches</span></div>
            <div class="search-section-items">
                <a href="{{ route('shop', ['search' => 'T-Shirt']) }}" class="search-tag">T-Shirt</a>
                <a href="{{ route('shop', ['search' => 'Polo']) }}" class="search-tag">Polo</a>
                <a href="{{ route('shop', ['search' => 'Hoodie']) }}" class="search-tag">Hoodie</a>
                <a href="{{ route('shop', ['search' => 'Joggers']) }}" class="search-tag">Joggers</a>
                <a href="{{ route('shop', ['search' => 'Kurti']) }}" class="search-tag">Kurti</a>
                <a href="{{ route('shop', ['search' => 'Shirt']) }}" class="search-tag">Shirt</a>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Side Menu -->
<div class="mobile-side-menu" id="sideMenu">
    <div class="side-menu-header">
        <div class="guest-greeting">
            @if(auth()->check() && !auth()->user()->is_admin)
                <span class="welcome-text">Hello, {{ auth()->user()->name }}</span>
                <div class="auth-buttons">
                    <a href="{{ route('customer.dashboard') }}" class="btn-login">My Account</a>
                    <a href="{{ route('customer.logout') }}" class="btn-register"
                       onclick="event.preventDefault(); document.getElementById('side-logout-form').submit();">Logout</a>
                    <form id="side-logout-form" action="{{ route('customer.logout') }}" method="POST" style="display:none;">@csrf</form>
                </div>
            @else
                <span class="welcome-text">Welcome to {{ site_name() }}</span>
                <div class="auth-buttons">
                    <a href="{{ route('customer.login') }}" class="btn-login">Login</a>
                    <a href="{{ route('customer.register') }}" class="btn-register">Register</a>
                </div>
            @endif
        </div>
        <button class="side-menu-close" id="closeSideMenu" type="button" aria-label="Close menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
        </button>
    </div>
    <div class="side-menu-content">
        <div class="side-menu-section">
            <a href="{{ route('home') }}" class="side-menu-item {{ $isHome ? 'highlight' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                <span>Home</span>
            </a>
            <a href="{{ route('shop') }}" class="side-menu-item {{ $isShop ? 'highlight' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                <span>Shop All</span>
            </a>
            <a href="{{ route('categories') }}" class="side-menu-item highlight">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2H2v10h10zM14 2h8v8h-8zM14 14h8v8h-8zM2 14h10v8H2z"></path></svg>
                <span>All Categories</span>
            </a>
        </div>

        <div class="side-menu-divider"></div>

        <div class="side-menu-section">
            <div class="side-menu-section-title">Categories</div>
            @foreach($navCats as $cat)
                <div class="side-menu-cat">
                    <button type="button" class="side-menu-item" @if($cat['children'])data-expand @endif>
                        <span style="width:8px; height:8px; border-radius:50%; background:{{ $cat['color'] }}; flex:none;"></span>
                        <span>{{ $cat['name'] }}</span>
                        @if($cat['children'])
                            <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"></path></svg>
                        @endif
                    </button>
                    @if($cat['children'])
                        <div class="side-menu-sub">
                            @foreach($cat['children'] as $child)
                                <a href="{{ $navCatUrl($child['slug']) }}">{{ $child['name'] }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="side-menu-divider"></div>

        <div class="side-menu-section">
            <div class="side-menu-section-title">My Account</div>
            <a href="{{ route('cart') }}" class="side-menu-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                <span>My Cart</span>
                <span class="side-menu-badge" data-cart-badge @if($cartCount == 0) style="display:none" @endif>{{ $cartCount }}</span>
            </a>
            <a href="{{ route('checkout') }}" class="side-menu-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v8m4-4-4-4-4 4"></path><path d="M3 21V9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12"></path></svg>
                <span>Checkout</span>
            </a>
            <a href="#" class="side-menu-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                <span>Contact Us</span>
            </a>
        </div>

        <div class="side-menu-divider"></div>

        <div class="side-menu-section">
            <a href="{{ $waLink }}" target="_blank" rel="noopener" class="side-menu-item highlight free-delivery">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                <span>Chat / WhatsApp</span>
            </a>
            <a href="{{ route('admin.login') }}" class="side-menu-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                <span>Admin</span>
            </a>
        </div>
    </div>
</div>
<div class="side-menu-overlay" id="sideMenuOverlay"></div>

<!-- Mobile Bottom Nav -->
<nav class="mobile-bottom-nav">
    <a href="{{ route('home') }}" class="bottom-nav-item {{ $isHome ? 'active' : '' }}" data-nav="home">
        <div class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
        </div>
        <span class="nav-label">Home</span>
    </a>

    <button class="bottom-nav-item {{ $isShop ? 'active' : '' }}" id="openCategoryMenu" type="button" data-nav="category">
        <div class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        </div>
        <span class="nav-label">Category</span>
    </button>

    <a href="{{ route('cart') }}" class="bottom-nav-item cart-nav-item {{ $isCart ? 'active' : '' }}" data-nav="cart">
        <div class="nav-icon cart-icon-wrapper">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
            <span class="cart-badge" data-cart-badge @if($cartCount == 0) style="display:none" @endif>{{ $cartCount }}</span>
        </div>
        <span class="nav-label">Cart</span>
    </a>

    <a href="{{ route('shop') }}" class="bottom-nav-item" data-nav="shop">
        <div class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
        </div>
        <span class="nav-label">Shop</span>
    </a>

    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="bottom-nav-item" data-nav="chat">
        <div class="nav-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
        </div>
        <span class="nav-label">Chat</span>
    </a>
</nav>

<!-- Mobile Mega Menu (bottom sheet) -->
<div class="mobile-mega-menu" id="megaMenu">
    <div class="mega-menu-backdrop" id="megaMenuBackdrop"></div>
    <div class="mega-menu-container">
        <div class="mega-menu-handle"><div class="handle-bar"></div></div>
        <div class="mega-menu-header">
            <h3 class="mega-menu-title" id="megaMenuTitle">Shop by Category</h3>
            <button class="mega-menu-close" id="closeMegaMenu" type="button" aria-label="Close categories">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18"></path><path d="m6 6 12 12"></path></svg>
            </button>
        </div>
        <div class="mega-menu-content">
            <div class="mega-menu-view" id="megaMainView">
                <div class="mega-menu-section quick-links">
                    <a href="{{ route('shop') }}" class="quick-link-card free-delivery">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        <span>Shop All</span>
                    </a>
                    <a href="{{ route('categories') }}" class="quick-link-card new-arrival">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2H2v10h10zM14 2h8v8h-8zM14 14h8v8h-8zM2 14h10v8H2z"></path></svg>
                        <span>Categories</span>
                    </a>
                    <a href="{{ route('cart') }}" class="quick-link-card top-selling">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                        <span>Cart</span>
                    </a>
                    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="quick-link-card mega-deal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        <span>Help</span>
                    </a>
                </div>

                <div class="mega-menu-section category-list">
                    <div class="section-title">All Categories</div>
                    @foreach($navCats as $cat)
                        <button type="button" class="category-item main-category" data-category="{{ $cat['id'] }}" data-name="{{ $cat['name'] }}" @if(!$cat['children']) onclick="window.location='{{ $navCatUrl($cat['slug']) }}'" @endif>
                            <div class="category-icon" style="background:{{ $cat['color'] }}1a; color:{{ $cat['color'] }};">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($cat['name'], 0, 1)) }}</div>
                            <div class="category-info">
                                <span class="category-name">{{ $cat['name'] }}</span>
                                <span class="category-count">{{ $cat['children'] ? $cat['name'] . ' subcategories & more' : 'Browse all ' . $cat['name'] }}</span>
                            </div>
                            @if($cat['children'])
                                <svg class="chevron-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"></path></svg>
                            @else
                                <svg class="chevron-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                            @endif
                        </button>
                    @endforeach
                    <a href="{{ route('shop') }}" class="category-item view-all">
                        <div class="category-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" x2="12" y1="22.08" y2="12"></line></svg>
                        </div>
                        <div class="category-info">
                            <span class="category-name">View All Products</span>
                            <span class="category-count">Browse entire catalog</span>
                        </div>
                        <svg class="chevron-ic" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"></path><path d="m12 5 7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>

            @foreach($navCats as $cat)
                <div class="mega-menu-view subcategory-view" id="megaSub-{{ $cat['id'] }}" style="display:none;" data-category-color="{{ $cat['color'] }}" data-category-theme="{{ $cat['id'] }}">
                    <button class="back-to-main" data-back="main" type="button">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg>
                        <span>{{ $cat['name'] }}</span>
                    </button>
                    <div class="subcategory-grid">
                        @foreach($cat['children'] as $child)
                            <a href="{{ $navCatUrl($child['slug']) }}" class="subcategory-card">
                                <span class="subcat-initial" style="background:{{ $cat['color'] }}">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($child['name'], 0, 1)) }}</span>
                                <span>{{ $child['name'] }}</span>
                            </a>
                        @endforeach
                        <a href="{{ $navCatUrl($cat['slug']) }}" class="subcategory-card view-all-sub">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                            <span>View All</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Desktop Header (Myntra style) -->
<header class="desktop-header">
    <div class="desktop-header-inner">
        <div class="header-logo">
            <a href="{{ route('home') }}">
                <img src="{{ site_logo() }}" alt="{{ site_name() }}">
            </a>
        </div>

        <nav class="header-nav-main">
            <ul class="nav-category-list">
                @foreach($navCats as $cat)
                    <li class="nav-category-item" style="--cat-color: {{ $cat['color'] }}">
                        <a href="{{ $navCatUrl($cat['slug']) }}" class="nav-category-link">{{ $cat['name'] }}</a>
                        <div class="mega-menu">
                            <div class="mega-menu-inner">
                                <div class="mega-menu-categories">
                                    <div class="mega-menu-column" style="--cat-color: {{ $cat['color'] }}">
                                        @if($cat['children'])
                                            <h4 class="mega-menu-heading"><a href="{{ $navCatUrl($cat['slug']) }}">{{ $cat['name'] }}</a></h4>
                                            <ul class="mega-menu-links">
                                                @foreach($cat['children'] as $child)
                                                    <li><a href="{{ $navCatUrl($child['slug']) }}">{{ $child['name'] }}</a></li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <h4 class="mega-menu-heading"><a href="{{ $navCatUrl($cat['slug']) }}">{{ $cat['name'] }}</a></h4>
                                        @endif
                                    </div>
                                </div>

                                <div class="mega-menu-products">
                                    <h4 class="mega-menu-products-heading">New Arrivals</h4>
                                    <div class="mega-menu-products-grid">
                                        @foreach($navCatProducts[$cat['id']] ?? [] as $navProduct)
                                            @php
                                                $navImg = $navProduct->images->where('is_featured', true)->first() ?? $navProduct->images->first();
                                                $navImgUrl = $navImg ? Storage::url($navImg->image) : null;
                                            @endphp
                                            <a href="{{ $navProduct->slug ? route('product.detail', $navProduct->slug) : '#' }}" class="mega-menu-product-card">
                                                <div class="mega-menu-product-image">
                                                    @if($navImgUrl)
                                                        <img src="{{ $navImgUrl }}" alt="{{ $navProduct->name }}" loading="lazy">
                                                    @else
                                                        <span class="mega-menu-product-ph"></span>
                                                    @endif
                                                </div>
                                                <div class="mega-menu-product-info">
                                                    <div class="mega-menu-product-title">{{ $navProduct->name }}</div>
                                                </div>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mega-menu-view-all">
                                    <a href="{{ $navCatUrl($cat['slug']) }}">View All {{ $cat['name'] }} →</a>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </nav>

        <form class="header-search" action="{{ route('shop') }}" method="get">
            <div class="header-search-form">
                <svg class="header-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                <input type="search" name="search" value="{{ request('search') }}" class="header-search-input" placeholder="Search for products, brands and more" autocomplete="off">
            </div>
        </form>

        <div class="header-actions">
            <div class="header-action-item has-dropdown">
                <span class="header-action-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                </span>
                @if(auth()->check() && !auth()->user()->is_admin)
                    <span class="header-action-label">{{ \Illuminate\Support\Str::limit(auth()->user()->name, 10) }}</span>
                @else
                    <span class="header-action-label">Profile</span>
                @endif
                <div class="header-profile-dropdown">
                    <div class="profile-dropdown-header">
                        @if(auth()->check() && !auth()->user()->is_admin)
                            <div class="welcome-text">Hello, {{ auth()->user()->name }}</div>
                            <div class="login-signup">
                                <a href="{{ route('customer.dashboard') }}">My Account</a>
                                <span>/</span>
                                <a href="{{ route('customer.logout') }}" onclick="event.preventDefault(); document.getElementById('desk-logout-form').submit();">Logout</a>
                                <form id="desk-logout-form" action="{{ route('customer.logout') }}" method="POST" style="display:none;">@csrf</form>
                            </div>
                        @else
                            <div class="welcome-text">Welcome to {{ site_name() }}</div>
                            <div class="login-signup">
                                <a href="{{ route('customer.login') }}">Login</a>
                                <span>/</span>
                                <a href="{{ route('customer.register') }}">Signup</a>
                            </div>
                        @endif
                    </div>
                    <div class="profile-dropdown-links">
                        @if(auth()->check() && !auth()->user()->is_admin)
                            <a href="{{ route('customer.dashboard') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                My Account
                            </a>
                            <a href="{{ route('customer.dashboard') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
                                My Orders
                            </a>
                        @endif
                        <a href="{{ route('cart') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                            My Cart
                        </a>
                        <a href="{{ route('checkout') }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v8m4-4-4-4-4 4"></path><path d="M3 21V9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v12"></path></svg>
                            Checkout
                        </a>
                        <a href="#">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            Contact Us
                        </a>
                    </div>
                </div>
            </div>

            <a href="#" class="header-action-item" id="cart-nav-btn" aria-label="Cart">
                <span class="header-action-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="21" r="1"></circle><circle cx="19" cy="21" r="1"></circle><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"></path></svg>
                    <span id="cart-badge" data-cart-badge class="desktop-cart-badge {{ $cartCount == 0 ? 'hidden' : '' }}">{{ $cartCount }}</span>
                </span>
                <span class="header-action-label">Cart</span>
            </a>
        </div>
    </div>
</header>

<script>
(function () {
    var bodyEl = document.body;

    function on(el, ev, cb) { if (el) el.addEventListener(ev, cb); }
    function toggle(el, add) { if (el) el.classList.toggle('open', add); }

    on(document.getElementById('closeSideMenu'), 'click', closeSide);
    on(document.getElementById('sideMenuOverlay'), 'click', closeSide);
    on(document.getElementById('openMobileMenu'), 'click', function () {
        toggle(document.getElementById('sideMenu'), true);
        toggle(document.getElementById('sideMenuOverlay'), true);
        bodyEl.classList.add('nav-lock');
    });

    function closeSide() {
        toggle(document.getElementById('sideMenu'), false);
        toggle(document.getElementById('sideMenuOverlay'), false);
        bodyEl.classList.remove('nav-lock');
    }

    on(document.getElementById('openSearchModal'), 'click', function () {
        toggle(document.getElementById('searchModal'), true);
        bodyEl.classList.add('nav-lock');
        setTimeout(function () {
            var i = document.getElementById('mobileSearchInput');
            if (i) i.focus();
        }, 250);
    });

    function closeSearch() {
        toggle(document.getElementById('searchModal'), false);
        bodyEl.classList.remove('nav-lock');
    }
    on(document.getElementById('closeSearchModal'), 'click', closeSearch);

    var searchInput = document.getElementById('mobileSearchInput');
    var clearBtn = document.getElementById('clearSearch');
    if (searchInput && clearBtn) {
        on(searchInput, 'input', function () {
            clearBtn.style.display = searchInput.value ? 'flex' : 'none';
        });
        on(clearBtn, 'click', function () {
            searchInput.value = '';
            clearBtn.style.display = 'none';
            searchInput.focus();
        });
    }

    var megaMenu = document.getElementById('megaMenu');
    var megaBackdrop = document.getElementById('megaMenuBackdrop');

    function openMega(subId) {
        document.querySelectorAll('.mega-menu-view').forEach(function (v) {
            v.style.display = 'none';
        });
        toggle(megaBackdrop, true);
        toggle(megaMenu, true);
        bodyEl.classList.add('nav-lock');
        if (subId) {
            setTimeout(function () {
                showSub(subId);
            }, 300);
        } else {
            var main = document.getElementById('megaMainView');
            var title = document.getElementById('megaMenuTitle');
            if (main) main.style.display = 'block';
            if (title) title.textContent = 'Shop by Category';
        }
    }

    function showSub(id) {
        document.querySelectorAll('.mega-menu-view').forEach(function (v) { v.style.display = 'none'; });
        var view = document.getElementById('megaSub-' + id);
        if (view) {
            view.style.display = 'block';
        } else {
            document.getElementById('megaMainView').style.display = 'block';
        }
        var btn = document.querySelector('.main-category[data-category="' + id + '"]');
        var title = document.getElementById('megaMenuTitle');
        if (title && btn) title.textContent = btn.getAttribute('data-name');
        if (!btn) title.textContent = 'Shop by Category';
    }

    function closeMega() {
        toggle(megaBackdrop, false);
        toggle(megaMenu, false);
        bodyEl.classList.remove('nav-lock');
        document.querySelectorAll('.mega-menu-view').forEach(function (v) { v.style.display = 'none'; });
        var main = document.getElementById('megaMainView');
        if (main) main.style.display = 'block';
        document.getElementById('megaMenuTitle').textContent = 'Shop by Category';
    }

    on(document.getElementById('openCategoryMenu'), 'click', function () { openMega(null); });
    on(document.getElementById('closeMegaMenu'), 'click', closeMega);
    on(megaBackdrop, 'click', closeMega);

    document.querySelectorAll('.main-category[data-category]').forEach(function (btn) {
        on(btn, 'click', function () {
            var id = btn.getAttribute('data-category');
            var view = document.getElementById('megaSub-' + id);
            if (view) showSub(id);
        });
    });

    document.querySelectorAll('[data-back="main"]').forEach(function (btn) {
        on(btn, 'click', function () {
            document.querySelectorAll('.mega-menu-view').forEach(function (v) { v.style.display = 'none'; });
            document.getElementById('megaMainView').style.display = 'block';
            document.getElementById('megaMenuTitle').textContent = 'Shop by Category';
        });
    });

    document.querySelectorAll('.side-menu-cat [data-expand]').forEach(function (btn) {
        on(btn, 'click', function () {
            var wrap = btn.closest('.side-menu-cat');
            if (!wrap) return;
            wrap.classList.toggle('open');
            var sub = wrap.querySelector('.side-menu-sub');
            if (sub) sub.classList.toggle('open');
        });
    });

    on(document, 'keydown', function (e) {
        if (e.key === 'Escape') {
            closeSide();
            closeSearch();
            closeMega();
        }
    });

    // Hide the fixed bottom nav while a field is focused so the on-screen
    // keyboard does not shove the bar above the input (mobile only).
    var keyboardMq = window.matchMedia('(max-width: 767.98px)');
    function setBottomNavHidden(hidden) {
        document.body.classList.toggle('keyboard-open', hidden);
    }
    function reEvaluateKeyboard() {
        var active = document.activeElement;
        var editable = active && typeof active.matches === 'function' && active.matches('input, select, textarea');
        setBottomNavHidden(keyboardMq.matches && !!editable);
    }
    document.addEventListener('focusin', function (e) {
        if (e.target && typeof e.target.matches === 'function' && e.target.matches('input, select, textarea')) {
            reEvaluateKeyboard();
        }
    });
    document.addEventListener('focusout', reEvaluateKeyboard);
    if (keyboardMq.addEventListener) {
        keyboardMq.addEventListener('change', reEvaluateKeyboard);
    } else if (keyboardMq.addListener) {
        keyboardMq.addListener(reEvaluateKeyboard);
    }
})();
</script>