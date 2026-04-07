<?php
include('connect.php');
$query = "SELECT * FROM products";
$all_product = $con->query($query);
$product_total_count = ($all_product && $all_product instanceof mysqli_result) ? mysqli_num_rows($all_product) : 0;
$product_search_term = isset($_GET['search']) ? trim((string) $_GET['search']) : '';
$product_search_query = function_exists('mb_strtolower') ? mb_strtolower($product_search_term) : strtolower($product_search_term);

$combo_table_ready = false;
$combo_products_table_ready = false;
$all_combos = false;

$combo_table_check = mysqli_query($con, "SHOW TABLES LIKE 'combos'");
if ($combo_table_check && mysqli_num_rows($combo_table_check) > 0) {
    $combo_table_ready = true;
}

$combo_products_table_check = mysqli_query($con, "SHOW TABLES LIKE 'combo_products'");
if ($combo_products_table_check && mysqli_num_rows($combo_products_table_check) > 0) {
    $combo_products_table_ready = true;
}

if ($combo_table_ready && $combo_products_table_ready) {
    $combo_query = "
        SELECT c.*, COUNT(cp.id) AS products_count
        FROM combos c
        LEFT JOIN combo_products cp ON cp.combo_id = c.id
        WHERE c.status = 1
        GROUP BY c.id
        ORDER BY c.id DESC
    ";
    $all_combos = $con->query($combo_query);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>ClassyCut Eshop</title>

    <!-- font awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <!-- box link -->
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        /* Full-width white section wrapper */
        .eshop-main-wrapper {
            background-color: #fff;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            position: relative;
            z-index: 2;
        }

        .product-main-container {
            display: flex;
            max-width: 1600px;
            /* Wider for more content space */
            margin: 0 auto;
            gap: 4rem;
            /* More breathing room between sidebar and products */
            padding: 4rem 40px;
            /* Reduced side padding (was clamp(1rem, 5vw, 5rem)) */
            align-items: flex-start;
            background: #fff;
        }

        .eshop-sidebar {
            width: 320px;
            flex-shrink: 0;
            position: sticky;
            top: 100px;
            /* Anchors fixed relative to top header */
            height: fit-content;
            z-index: 10;
        }

        .eshop-content {
            flex: 1;
            min-width: 0;
            max-height: 800px; /* Initial fallback, synced by JS to match Sidebar */
            /* Independent scroll for products */
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 2rem;
            /* Space for the scrollbar and breathing room */
            overscroll-behavior-y: auto;
            /* Native scroll chaining: once bottom is reached, page scrolls */
            scroll-behavior: smooth;
        }

        /* Custom Scrollbar for Product Listing Area */
        .eshop-content::-webkit-scrollbar {
            width: 5px;
        }

        .eshop-content::-webkit-scrollbar-track {
            background: rgba(240, 238, 234, 0.5);
            border-radius: 10px;
        }

        .eshop-content::-webkit-scrollbar-thumb {
            background: var(--brand);
            border-radius: 10px;
        }

        .eshop-filter-panel {
            background: #ffffff;
            border: 1px solid rgba(203, 185, 15, 0.22);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.05);
        }

        .eshop-filter-section {
            margin-bottom: 2.25rem;
            padding-bottom: 2rem;
            border-bottom: 1.5px dashed #f0eeea;
        }

        .eshop-filter-section:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .eshop-filter-section-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--bg1);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-transform: none;
        }

        .eshop-filter-section-title i {
            color: var(--brand);
            font-size: 0.9rem;
        }

        .eshop-field {
            display: flex;
            flex-direction: column;
            gap: 0.6rem;
        }

        .eshop-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .eshop-input-wrap i {
            position: absolute;
            left: 1.1rem;
            color: #8c8562;
            font-size: 0.95rem;
            pointer-events: none;
        }

        .eshop-input-wrap input,
        .eshop-input-wrap select {
            width: 100%;
            height: 52px;
            border-radius: 14px;
            border: 1.5px solid #eceae0;
            background: #fdfdfa;
            color: var(--bg1);
            font-size: 0.96rem;
            padding: 0 1rem 0 2.8rem;
            text-transform: none;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .eshop-input-wrap select {
            cursor: pointer;
            appearance: none;
            padding-right: 2.5rem;
        }

        .eshop-input-wrap:has(select)::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 1.2rem;
            color: #8c8562;
            pointer-events: none;
        }

        .eshop-input-wrap input:focus,
        .eshop-input-wrap select:focus {
            outline: none;
            border-color: var(--brand);
            background: #fff;
            box-shadow: 0 0 0 5px rgba(203, 185, 15, 0.12);
        }

        .eshop-size-rail {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0.75rem;
        }

        .eshop-size-chip {
            border: 1.5px solid #eceae0;
            background: #fdfdfa;
            color: #5a5438;
            padding: 0.75rem;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 700;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s ease;
        }

        .eshop-size-chip:hover:not(.is-active) {
            border-color: var(--brand);
            background: #fffdf3;
            transform: scale(1.02);
        }

        .eshop-size-chip.is-active {
            background: var(--brand);
            color: var(--bg1);
            border-color: var(--brand);
            box-shadow: 0 6px 15px rgba(203, 185, 15, 0.28);
        }

        .eshop-range-label {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--bg1);
            margin-bottom: 1rem;
            display: block;
        }

        .eshop-range-slider {
            width: 100%;
            accent-color: var(--brand);
            height: 8px;
            border-radius: 4px;
            background: #f2f2f2;
            margin-bottom: 1rem;
        }

        .eshop-reset-btn {
            width: 100%;
            height: 52px;
            border: 2px solid #f2f2f2;
            background: #fff;
            color: #888;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-top: 1.5rem;
        }

        .eshop-reset-btn:hover {
            border-color: #ff5252;
            color: #ff5252;
            background: rgba(255, 82, 82, 0.05);
            transform: translateY(-2px);
        }

        /* Listing Header & Results */
        .eshop-listing-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid #f8f8f8;
        }

        .eshop-listing-title {
            font-size: 2.1rem;
            color: var(--bg1);
            font-weight: 900;
            letter-spacing: -0.03em;
            text-transform: none;
            position: relative;
        }

        .eshop-listing-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -1.6rem;
            width: 100px;
            height: 4px;
            background: var(--brand);
            border-radius: 2px;
        }

        .eshop-result-pill {
            background: #fdfdfa;
            border: 1.5px solid #eceae0;
            color: #8c8562;
            padding: 0.65rem 1.4rem;
            border-radius: 999px;
            font-size: 0.92rem;
            font-weight: 700;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
        }

        /* Pagination Styling matching Admin */
        .pagination-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 5rem;
            margin-bottom: 1rem;
            gap: 10px;
        }

        .pagination-item {
            padding: 10px 20px;
            border: 1.5px solid #f2f2f2;
            border-radius: 10px;
            color: var(--bg1);
            font-weight: 700;
            transition: all 0.2s;
            background: #fff;
            text-decoration: none;
            cursor: pointer;
            font-size: 0.95rem;
        }

        .pagination-item:hover {
            background: var(--brand);
            color: var(--bg1);
            border-color: var(--brand);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(203, 185, 15, 0.3);
        }

        .pagination-item.active {
            background: var(--bg1);
            color: var(--white);
            border-color: var(--bg1);
            box-shadow: 0 6px 18px rgba(24, 21, 13, 0.25);
        }

        .pagination-item.disabled {
            color: #ddd;
            pointer-events: none;
            border-color: #f8f8f8;
            opacity: 0.7;
        }

        /* Combo Section refinement (Outside Main Content) */
        .eshop-combos-section {
            background: #fdfdfa;
            padding: 6rem clamp(1.5rem, 5vw, 5rem) 8rem;
            border-top: 1px solid #f2f2f2;
            width: 100%;
        }

        .eshop-combos-section .eshop-section-head {
            text-align: center;
            margin-bottom: 4.5rem;
        }

        .eshop-combos-section h2 {
            font-size: 2.8rem;
            color: var(--bg1);
            font-weight: 900;
            margin-bottom: 1.25rem;
            letter-spacing: -0.04em;
        }

        .eshop-combos-section p {
            color: #6d6644;
            max-width: 650px;
            margin: 0 auto;
            font-size: 1.15rem;
            line-height: 1.7;
        }

        .combo-grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr) !important;
            gap: 2rem !important;
            padding: 0 !important;
            max-width: 1600px;
            margin: 0 auto;
        }

        .product-main-container .product-container {
            padding: 0 !important;
            display: grid !important;
            grid-template-columns: repeat(3, 1fr) !important;
            /* Forces 3 products per row for a balanced density */
            gap: 2.5rem 2rem !important;
        }

        @media (max-width: 1200px) {
            .combo-grid-4 {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        @media (max-width: 1100px) {
            .product-main-container {
                flex-direction: column;
                padding: 3rem 1.5rem;
                gap: 4rem;
            }

            .eshop-sidebar {
                width: 100%;
                position: static;
            }

            .eshop-size-rail {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
        }

        @media (max-width: 600px) {
            .combo-grid-4 {
                grid-template-columns: 1fr !important;
            }
        }

        @media (max-width: 992px) {
            .eshop-filter-grid {
                grid-template-columns: 1fr;
            }

            .eshop-filter-footer {
                grid-template-columns: 1fr;
            }

            .eshop-filter-top {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>

    <!-- header and navigation section -->

    <?php
    include('header.php');
    ?>

    <!-- header and navigation section -->

    <!-- defualt section -->
    <div class="defualt-section">
        <img src="photos/about-img1.jpeg" alt="" class="img">
        <div class="img-content">
            <h2>Men's Grooming Products</h2>
            <div class="menu">
                <a href="index.php">HOME</a> / <span>Our E-shop Products</span>
            </div>

        </div>

    </div>


    <!-- default section -->

    <!-- /* product section */ -->

    <div class="eshop-main-wrapper">
        <?php if ($product_search_term !== ''): ?>
            <div class="search-feedback search-feedback--light">
                Showing product results for <strong><?php echo htmlspecialchars($product_search_term, ENT_QUOTES); ?></strong>.
            </div>
        <?php endif; ?>
        <div class="product-main-container">
            <!-- Professional Sidebar Filter -->
            <aside class="eshop-sidebar" id="eshopFilterRoot">
                <div class="eshop-filter-panel">
                    <!-- Search Section -->
                    <div class="eshop-filter-section">
                        <h3 class="eshop-filter-section-title"><i class="fas fa-search"></i> Search Products</h3>
                        <div class="eshop-field">
                            <div class="eshop-input-wrap">
                                <i class="fas fa-search"></i>
                                <input
                                    type="text"
                                    id="eshopSearchInput"
                                    placeholder="E.g. Hair Wax..."
                                    value="<?php echo htmlspecialchars($product_search_term, ENT_QUOTES); ?>"
                                    data-initial-query="<?php echo htmlspecialchars($product_search_term, ENT_QUOTES); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Sort Section -->
                    <div class="eshop-filter-section">
                        <h3 class="eshop-filter-section-title"><i class="fas fa-sort-amount-down"></i> Sort Products</h3>
                        <div class="eshop-field">
                            <div class="eshop-input-wrap">
                                <i class="fas fa-filter"></i>
                                <select id="eshopSortSelect">
                                    <option value="relevance">Default Sorting</option>
                                    <option value="price_asc">Price: Low to High</option>
                                    <option value="price_desc">Price: High to Low</option>
                                    <option value="name_asc">Name: A to Z</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Price Section -->
                    <div class="eshop-filter-section">
                        <h3 class="eshop-filter-section-title"><i class="fas fa-tag"></i> Price Range</h3>
                        <div class="eshop-range-wrap">
                            <input type="range" min="0" step="1" id="eshopPriceRange" class="eshop-range-slider">
                            <span class="eshop-range-label" id="eshopRangeLabel">Up to ₹ 0</span>
                            <div class="eshop-input-wrap">
                                <i class="fas fa-indian-rupee-sign"></i>
                                <input type="number" id="eshopPriceMax" placeholder="Max Price">
                            </div>
                        </div>
                    </div>

                    <!-- Size Section -->
                    <div class="eshop-filter-section">
                        <h3 class="eshop-filter-section-title"><i class="fas fa-ruler-combined"></i> Filter By Size</h3>
                        <div class="eshop-size-rail" id="eshopSizeFilters">
                            <!-- Chips injected by JS -->
                        </div>
                    </div>

                    <button type="button" class="eshop-reset-btn" id="eshopResetBtn">
                        <i class="fas fa-rotate-left"></i> Reset All Filters
                    </button>
                </div>
            </aside>

            <!-- Main Content Area -->
            <main class="eshop-content">
                <!-- Listing Header -->
                <div class="eshop-listing-header">
                    <h2 class="eshop-listing-title">Our Exclusive Collection</h2>
                    <span class="eshop-result-pill" id="eshopResultCount"><?php echo (int) $product_total_count; ?> products</span>
                </div>

                <div class="product-container" id="eshopProductGrid">
                    <!-- display product -->
                    <?php
                    $all_product->data_seek(0); // Reset pointer
                    while ($row = mysqli_fetch_assoc($all_product)) {
                        $original_price = (float) $row["p_price"];
                        $discount = isset($row["p_discount"]) ? (float) $row["p_discount"] : 0;
                        $discounted_price = $original_price - (($original_price * $discount) / 100);
                        $filter_price = $discount > 0 ? $discounted_price : $original_price;
                        $filter_name = strtolower(trim((string) $row["p_name"]));
                        $filter_desc = strtolower(trim((string) $row["p_desc"]));
                        $filter_size = trim((string) $row["p_size"]);
                        $filter_blob = trim($filter_name . " " . $filter_desc . " " . strtolower($filter_size));
                        $is_search_match = ($product_search_query !== '' && strpos($filter_blob, $product_search_query) !== false);
                    ?>
                        <div class="product-card<?php echo $is_search_match ? ' search-match-highlight' : ''; ?>"
                            data-product-card="true"
                            data-name="<?php echo htmlspecialchars($filter_name, ENT_QUOTES); ?>"
                            data-search="<?php echo htmlspecialchars($filter_blob, ENT_QUOTES); ?>"
                            data-size="<?php echo htmlspecialchars($filter_size, ENT_QUOTES); ?>"
                            data-price="<?php echo number_format($filter_price, 2, '.', ''); ?>"
                            data-query-match="<?php echo $is_search_match ? '1' : '0'; ?>">
                            <img src="upload_product_photos/<?php echo $row["p_img"]; ?>" alt="Product">
                            <h3><?php echo $row["p_name"]; ?></h3>
                            <p class="content"><?php echo $row["p_desc"]; ?></p>
                            <p class="eshop-price-wrap">
                                <?php if ($discount > 0): ?>
                                    <span class="eshop-price-original">₹ <?php echo number_format($original_price, 2); ?></span>
                                    <span class="eshop-price-final">₹ <?php echo number_format($discounted_price, 2); ?></span>
                                    <span class="eshop-discount-badge"><?php echo number_format($discount, 0); ?>% OFF</span>
                                <?php else: ?>
                                    <span class="eshop-price-final">₹ <?php echo number_format($original_price, 2); ?></span>
                                <?php endif; ?>
                                <i> ( <?php echo $row["p_size"]; ?> )</i>
                            </p>
                            <a href="product_display.php?id=<?php echo $row["p_id"]; ?>">
                                <button>View Details</button>
                            </a>
                        </div>
                    <?php
                    }
                    ?>
                </div>

                <div class="eshop-empty-state" id="eshopEmptyState" hidden>
                    No products match your filters. Try resetting or selecting different criteria.
                </div>

                <!-- JS Managed Pagination Container -->
                <div id="eshopPagination" class="pagination-container"></div>
            </main>
        </div>

        <!-- Premium Combos Section (Now Outside the Flex Container) -->
        <section class="eshop-combos-section">
            <div class="eshop-section-head">
                <h2><i class="fas fa-gem"></i> Unlock Premium Combos</h2>
                <p>Elevate your grooming routine with our handcrafted bundles, offering the ultimate luxury and value in one package.</p>
            </div>

            <div class="product-container combo-grid-4">
                <?php if (!$combo_table_ready || !$combo_products_table_ready): ?>
                    <div class="combo-empty-note">
                        Premium combos are not available yet.
                    </div>
                <?php elseif ($all_combos && mysqli_num_rows($all_combos) > 0): ?>
                    <?php while ($combo_row = mysqli_fetch_assoc($all_combos)): ?>
                        <div class="product-card combo-card">
                            <img src="upload_product_photos/<?php echo htmlspecialchars(!empty($combo_row['image']) ? $combo_row['image'] : 'default.jpeg'); ?>" alt="Combo Image">
                            <h3><?php echo htmlspecialchars($combo_row['name']); ?></h3>
                            <p class="content"><?php echo htmlspecialchars($combo_row['description']); ?></p>
                            <p class="combo-meta-line"><?php echo (int) $combo_row['products_count']; ?> products included</p>
                            <p>₹ <?php echo number_format((float) $combo_row['price'], 2); ?></p>
                            <a href="combo_display.php?id=<?php echo (int) $combo_row['id']; ?>">
                                <button>View Details</button>
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="combo-empty-note">
                        No premium combos available right now.
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <!-- /* product section */ -->


    <!-- footer sections -->


    <?php
    include('footer.php');
    ?>


    <!-- footer sections -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const grid = document.getElementById('eshopProductGrid');
            const filterRoot = document.getElementById('eshopFilterRoot');
            if (!grid || !filterRoot) return;

            const searchInput = document.getElementById('eshopSearchInput');
            const sortSelect = document.getElementById('eshopSortSelect');
            const maxPriceInput = document.getElementById('eshopPriceMax');
            const priceRange = document.getElementById('eshopPriceRange');
            const sizeRail = document.getElementById('eshopSizeFilters');
            const resultCount = document.getElementById('eshopResultCount');
            const rangeLabel = document.getElementById('eshopRangeLabel');
            const resetButton = document.getElementById('eshopResetBtn');
            const emptyState = document.getElementById('eshopEmptyState');
            const initialQuery = searchInput ? (searchInput.dataset.initialQuery || '').trim() : '';

            if (searchInput && initialQuery && !searchInput.value.trim()) {
                searchInput.value = initialQuery;
            }

            const cards = Array.from(grid.querySelectorAll('[data-product-card="true"]'));
            if (!cards.length) {
                if (resultCount) {
                    resultCount.textContent = '0 products';
                }
                if (emptyState) {
                    const activeQuery = searchInput ? searchInput.value.trim() : '';
                    emptyState.textContent = activeQuery
                        ? 'No products found for "' + activeQuery + '". Try a different keyword.'
                        : 'No products are available right now.';
                    emptyState.hidden = false;
                }
                return;
            }

            cards.forEach(function(card, index) {
                card.dataset.order = String(index);
            });

            const priceList = cards
                .map(function(card) {
                    return parseFloat(card.dataset.price || '0');
                })
                .filter(function(value) {
                    return !Number.isNaN(value);
                });

            const highestPrice = priceList.length ? Math.max.apply(null, priceList) : 0;
            const maxPriceLimit = Math.max(100, Math.ceil(highestPrice / 50) * 50);

            maxPriceInput.max = String(maxPriceLimit);
            maxPriceInput.value = String(maxPriceLimit);
            priceRange.max = String(maxPriceLimit);
            priceRange.value = String(maxPriceLimit);
            rangeLabel.textContent = "Up to \u20B9 " + maxPriceLimit;

            const uniqueSizes = Array.from(
                new Set(
                    cards
                    .map(function(card) {
                        return (card.dataset.size || '').trim();
                    })
                    .filter(function(size) {
                        return size !== '';
                    })
                )
            ).sort(function(a, b) {
                return a.localeCompare(b, undefined, {
                    numeric: true,
                    sensitivity: 'base'
                });
            });

            const activateChip = function(target) {
                const chips = sizeRail.querySelectorAll('.eshop-size-chip');
                chips.forEach(function(chip) {
                    chip.classList.remove('is-active');
                });
                target.classList.add('is-active');
            };

            const appendChip = function(label, value, active) {
                const chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'eshop-size-chip' + (active ? ' is-active' : '');
                chip.dataset.size = value;
                chip.textContent = label;
                sizeRail.appendChild(chip);
            };

            appendChip('All Sizes', 'all', true);
            uniqueSizes.forEach(function(size) {
                appendChip(size, size, false);
            });

            const sortCards = function(mode) {
                const sorted = cards.slice().sort(function(a, b) {
                    const priceA = parseFloat(a.dataset.price || '0');
                    const priceB = parseFloat(b.dataset.price || '0');
                    const nameA = (a.dataset.name || '').toLowerCase();
                    const nameB = (b.dataset.name || '').toLowerCase();
                    const orderA = parseInt(a.dataset.order || '0', 10);
                    const orderB = parseInt(b.dataset.order || '0', 10);

                    if (mode === 'price_asc') return priceA - priceB;
                    if (mode === 'price_desc') return priceB - priceA;
                    if (mode === 'name_asc') return nameA.localeCompare(nameB);
                    return orderA - orderB;
                });

                sorted.forEach(function(card) {
                    grid.appendChild(card);
                });
            };

            const paginationWrapper = document.getElementById('eshopPagination');
            let currentPage = 1;
            const itemsPerPage = 12;

            const applyPagination = function(visibleCards) {
                const totalItems = visibleCards.length;
                const totalPages = Math.ceil(totalItems / itemsPerPage);

                if (currentPage > totalPages) currentPage = Math.max(1, totalPages);

                const startIdx = (currentPage - 1) * itemsPerPage;
                const endIdx = startIdx + itemsPerPage;

                visibleCards.forEach(function(card, index) {
                    card.style.display = (index >= startIdx && index < endIdx) ? '' : 'none';
                });

                renderPaginationUI(totalPages);
            };

            const renderPaginationUI = function(totalPages) {
                paginationWrapper.innerHTML = '';
                if (totalPages <= 1) return;

                // Previous
                const prev = document.createElement('span');
                prev.className = 'pagination-item' + (currentPage === 1 ? ' disabled' : '');
                prev.innerHTML = '<i class="fas fa-chevron-left"></i>';
                if (currentPage > 1) {
                    prev.onclick = function() {
                        currentPage--;
                        runFilters();
                        window.scrollTo({
                            top: grid.offsetTop - 120,
                            behavior: 'smooth'
                        });
                    };
                }
                paginationWrapper.appendChild(prev);

                // Pages
                let startPage = Math.max(1, currentPage - 2);
                let endPage = Math.min(totalPages, startPage + 4);
                if (endPage - startPage < 4) startPage = Math.max(1, endPage - 4);

                for (let i = startPage; i <= endPage; i++) {
                    const pageItem = document.createElement('span');
                    pageItem.className = 'pagination-item' + (i === currentPage ? ' active' : '');
                    pageItem.textContent = i;
                    pageItem.onclick = function() {
                        if (currentPage === i) return;
                        currentPage = i;
                        runFilters();
                        window.scrollTo({
                            top: grid.offsetTop - 120,
                            behavior: 'smooth'
                        });
                    };
                    paginationWrapper.appendChild(pageItem);
                }

                // Next
                const next = document.createElement('span');
                next.className = 'pagination-item' + (currentPage === totalPages ? ' disabled' : '');
                next.innerHTML = '<i class="fas fa-chevron-right"></i>';
                if (currentPage < totalPages) {
                    next.onclick = function() {
                        currentPage++;
                        runFilters();
                        window.scrollTo({
                            top: grid.offsetTop - 120,
                            behavior: 'smooth'
                        });
                    };
                }
                paginationWrapper.appendChild(next);
            };

            const applyFilters = function() {
                const searchTerm = (searchInput.value || '').trim().toLowerCase();
                const activeChip = sizeRail.querySelector('.eshop-size-chip.is-active');
                const activeSize = activeChip ? (activeChip.dataset.size || '').toLowerCase() : 'all';

                let currentMaxPrice = parseFloat(maxPriceInput.value || String(maxPriceLimit));
                if (Number.isNaN(currentMaxPrice) || currentMaxPrice < 0) {
                    currentMaxPrice = maxPriceLimit;
                }
                currentMaxPrice = Math.min(currentMaxPrice, maxPriceLimit);

                maxPriceInput.value = String(Math.round(currentMaxPrice));
                priceRange.value = String(Math.round(currentMaxPrice));
                rangeLabel.textContent = "Up to \u20B9 " + Math.round(currentMaxPrice);

                const visibleCards = [];
                cards.forEach(function(card) {
                    const searchableText = (card.dataset.search || '').toLowerCase();
                    const sizeText = (card.dataset.size || '').toLowerCase();
                    const price = parseFloat(card.dataset.price || '0');

                    const matchesSearch = !searchTerm || searchableText.includes(searchTerm);
                    const matchesSize = activeSize === 'all' || sizeText === activeSize;
                    const matchesPrice = price <= currentMaxPrice;
                    const shouldShow = matchesSearch && matchesSize && matchesPrice;

                    card.classList.toggle('search-match-highlight', Boolean(searchTerm) && matchesSearch);

                    if (shouldShow) visibleCards.push(card);
                    else card.style.display = 'none';
                });

                resultCount.textContent = visibleCards.length + (visibleCards.length === 1 ? ' product found' : ' products found');
                if (visibleCards.length === 0) {
                    const activeQuery = (searchInput.value || '').trim();
                    emptyState.textContent = activeQuery
                        ? 'No products found for "' + activeQuery + '". Try another keyword or reset filters.'
                        : 'No products match your filters. Try resetting or selecting different criteria.';
                }
                emptyState.hidden = visibleCards.length !== 0;

                applyPagination(visibleCards);
            };

            const runFilters = function() {
                sortCards(sortSelect.value);
                applyFilters();
            };

            sizeRail.addEventListener('click', function(event) {
                const target = event.target.closest('.eshop-size-chip');
                if (!target) return;
                activateChip(target);
                currentPage = 1; // Reset to page 1 on filter change
                runFilters();
            });

            searchInput.addEventListener('input', function() {
                currentPage = 1;
                applyFilters();
            });
            sortSelect.addEventListener('change', function() {
                currentPage = 1;
                runFilters();
            });
            maxPriceInput.addEventListener('input', function() {
                currentPage = 1;
                applyFilters();
            });

            priceRange.addEventListener('input', function() {
                maxPriceInput.value = priceRange.value;
                currentPage = 1;
                applyFilters();
            });

            resetButton.addEventListener('click', function() {
                searchInput.value = '';
                sortSelect.value = 'relevance';
                maxPriceInput.value = String(maxPriceLimit);
                priceRange.value = String(maxPriceLimit);
                const allChip = sizeRail.querySelector('[data-size="all"]');
                if (allChip) activateChip(allChip);
                currentPage = 1;
                runFilters();
            });

            runFilters();
            // --- Height Syncing & Scroll Chaining Logic ---
            const eshopContent = document.querySelector('.eshop-content');
            const filterPanel = document.querySelector('.eshop-filter-panel');

            const syncHeights = function() {
                if (!eshopContent || !filterPanel) return;
                // Match products display height EXACTLY to the filter panel's height
                if (window.innerWidth > 1100) {
                    const panelHeight = filterPanel.offsetHeight;
                    eshopContent.style.maxHeight = panelHeight + 'px';
                } else {
                    eshopContent.style.maxHeight = 'none';
                }
            };

            // Sync on load and resize
            syncHeights();
            window.addEventListener('resize', syncHeights);

            if (eshopContent) {
                eshopContent.addEventListener('wheel', function(e) {
                    const delta = e.deltaY;
                    const contentHeight = this.scrollHeight;
                    const containerHeight = this.offsetHeight;
                    const scrollTop = this.scrollTop;

                    // If scrolling down at the bottom OR up at the top, let the window scroll
                    const isAtBottom = (scrollTop + containerHeight >= contentHeight - 1);
                    const isAtTop = (scrollTop <= 0);

                    if ((delta > 0 && isAtBottom) || (delta < 0 && isAtTop)) {
                        // Let the event propagate to the window
                        return;
                    }

                    // Otherwise, prevent window scroll while we are inside the products area
                    e.stopPropagation();
                }, {
                    passive: true
                });
            }
        });
    </script>
    <script src="js/script.js"></script>
</body>

</html>
