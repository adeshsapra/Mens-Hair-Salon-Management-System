<?php
include('connect.php');
$query = "SELECT * FROM products";
$all_product = $con->query($query);
$product_total_count = ($all_product && $all_product instanceof mysqli_result) ? mysqli_num_rows($all_product) : 0;

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
        .eshop-filter-zone {
            padding: 2rem clamp(0.75rem, 3vw, 1.5rem) 0.5rem;
        }

        .eshop-filter-panel {
            position: relative;
            overflow: hidden;
            border-radius: 16px;
            border: 1px solid rgba(203, 185, 15, 0.35);
            background:
                radial-gradient(circle at 85% -10%, rgba(203, 185, 15, 0.22) 0%, rgba(203, 185, 15, 0) 40%),
                linear-gradient(120deg, rgba(24, 21, 13, 0.98) 0%, rgba(32, 28, 17, 0.97) 100%);
            box-shadow: 0 14px 32px rgba(0, 0, 0, 0.16);
            padding: 1.35rem;
        }

        .eshop-filter-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .product-main-container .eshop-filter-title {
            margin: 0;
            color: var(--bg2);
            font-size: clamp(1.05rem, 2vw, 1.3rem);
            letter-spacing: 0.02em;
            text-transform: none;
        }

        .product-main-container .eshop-filter-subtitle {
            margin: 0.2rem 0 0;
            color: rgba(234, 227, 194, 0.82);
            font-size: 0.92rem;
            text-align: left;
            line-height: 1.45;
            text-transform: none;
            font-weight: 400;
        }

        .eshop-result-pill {
            border-radius: 999px;
            border: 1px solid rgba(203, 185, 15, 0.48);
            background: rgba(203, 185, 15, 0.14);
            color: #f5f1dc;
            padding: 0.44rem 0.85rem;
            font-size: 0.82rem;
            font-weight: 600;
            text-transform: none;
            white-space: nowrap;
        }

        .eshop-filter-grid {
            display: grid;
            grid-template-columns: 1.4fr 0.75fr 0.85fr;
            gap: 0.8rem;
        }

        .eshop-field {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .eshop-field label {
            color: rgba(234, 227, 194, 0.88);
            font-size: 0.74rem;
            letter-spacing: 0.08em;
            font-weight: 600;
            text-transform: uppercase;
        }

        .eshop-input-wrap {
            display: flex;
            align-items: center;
            position: relative;
        }

        .eshop-input-wrap i {
            position: absolute;
            left: 0.8rem;
            color: rgba(24, 21, 13, 0.65);
            font-size: 0.86rem;
            pointer-events: none;
        }

        .eshop-input-wrap input,
        .eshop-input-wrap select {
            width: 100%;
            height: 44px;
            border-radius: 10px;
            border: 1px solid rgba(24, 21, 13, 0.1);
            background: #fffdf3;
            color: var(--bg1);
            font-size: 0.94rem;
            padding: 0 0.75rem 0 2.25rem;
            text-transform: none;
            font-weight: 500;
        }

        .eshop-input-wrap select {
            cursor: pointer;
        }

        .eshop-input-wrap input:focus,
        .eshop-input-wrap select:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(203, 185, 15, 0.2);
            background: #fff;
        }

        .eshop-filter-footer {
            margin-top: 0.9rem;
            display: grid;
            grid-template-columns: 1.25fr 1fr auto;
            gap: 0.9rem;
            align-items: end;
        }

        .eshop-size-rail {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
        }

        .eshop-size-chip {
            border: 1px solid rgba(234, 227, 194, 0.35);
            background: rgba(255, 255, 255, 0.05);
            color: #ece5c8;
            padding: 0.38rem 0.75rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            text-transform: none;
        }

        .eshop-size-chip.is-active {
            background: var(--brand);
            color: var(--bg1);
            border-color: var(--brand);
            font-weight: 700;
        }

        .eshop-range-wrap {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .eshop-range-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
        }

        .eshop-range-label {
            color: rgba(234, 227, 194, 0.95);
            font-size: 0.84rem;
            font-weight: 600;
            text-transform: none;
        }

        .eshop-range-input {
            width: 110px;
            height: 38px;
            border-radius: 8px;
            border: 1px solid rgba(24, 21, 13, 0.1);
            background: #fffdf3;
            color: var(--bg1);
            font-size: 0.9rem;
            text-align: center;
            text-transform: none;
            font-weight: 600;
        }

        .eshop-range-input:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(203, 185, 15, 0.2);
        }

        .eshop-range-slider {
            width: 100%;
            accent-color: var(--brand);
            cursor: pointer;
        }

        .eshop-reset-btn {
            height: 38px;
            border: 1px solid rgba(203, 185, 15, 0.62);
            background: transparent;
            color: #f3ebcf;
            border-radius: 9px;
            padding: 0 1rem;
            font-size: 0.85rem;
            cursor: pointer;
            font-weight: 600;
            text-transform: none;
        }

        .eshop-reset-btn:hover {
            background: rgba(203, 185, 15, 0.14);
            color: #fff;
        }

        .eshop-empty-state {
            margin: 0 0 1.6rem;
            text-align: center;
            background: #fff7d6;
            border: 1px dashed rgba(203, 185, 15, 0.8);
            color: #5a4f08;
            border-radius: 10px;
            padding: 0.95rem 1rem;
            font-size: 0.95rem;
            font-weight: 600;
            text-transform: none;
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

    <div class="product-main-container">
        <div class="eshop-filter-zone" id="eshopFilterRoot">
            <div class="eshop-filter-panel">
                <div class="eshop-filter-top">
                    <div>
                        <h2 class="eshop-filter-title">Shop Smarter With Quick Filters</h2>
                        <p class="eshop-filter-subtitle">Search by product name, size, or budget to find what you need faster.</p>
                    </div>
                    <span class="eshop-result-pill" id="eshopResultCount"><?php echo (int) $product_total_count; ?> products found</span>
                </div>

                <div class="eshop-filter-grid">
                    <div class="eshop-field">
                        <label for="eshopSearchInput">Search Product</label>
                        <div class="eshop-input-wrap">
                            <i class="fas fa-search"></i>
                            <input type="text" id="eshopSearchInput" placeholder="Try: wax, beard oil, face mask...">
                        </div>
                    </div>
                    <div class="eshop-field">
                        <label for="eshopSortSelect">Sort By</label>
                        <div class="eshop-input-wrap">
                            <i class="fas fa-arrow-down-wide-short"></i>
                            <select id="eshopSortSelect">
                                <option value="relevance">Default Order</option>
                                <option value="price_asc">Price: Low to High</option>
                                <option value="price_desc">Price: High to Low</option>
                                <option value="name_asc">Name: A to Z</option>
                            </select>
                        </div>
                    </div>
                    <div class="eshop-field">
                        <label for="eshopPriceMax">Max Price (₹)</label>
                        <div class="eshop-input-wrap">
                            <i class="fas fa-indian-rupee-sign"></i>
                            <input type="number" id="eshopPriceMax" min="0" step="1">
                        </div>
                    </div>
                </div>

                <div class="eshop-filter-footer">
                    <div class="eshop-size-rail" id="eshopSizeFilters"></div>
                    <div class="eshop-range-wrap">
                        <div class="eshop-range-top">
                            <span class="eshop-range-label" id="eshopRangeLabel"></span>
                            <button type="button" class="eshop-reset-btn" id="eshopResetBtn">Reset Filters</button>
                        </div>
                        <input type="range" min="0" step="1" id="eshopPriceRange" class="eshop-range-slider">
                    </div>
                </div>
            </div>
        </div>

        <div class="product-container" id="eshopProductGrid">

            <!-- <div class="product-card">
                <img src="products/hairpowder.jpg" alt="Product 2">
                <h3>Hair Volumizing Powder</h3>
                <p class="content">ClassyCut's volumizing powder wax adds instant lift and texture with a lightweight, natural feel.</p>
                <p>₹ 349 <i> ( 100ml )</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/hairoil.jpg" alt="Product 2">
                <h3>Hair Oil</h3>
                <p class="content">ClassyCut's Hair Oil nourishes and protects your hair with a luxurious, silky smooth finish.</p>
                <p>₹ 299 <i> ( 100ml )</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/hairsprey.jpg" alt="Product 3">
                <h3>Hair Spray</h3>
                <p class="content"> ClassyCut's Strong Hold Hair Spray, a fast-drying, non-sticky formula that keeps your look in place all day.</p>
                <p>₹ 499 <i> ( 100ml )</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/wax.jpg" alt="Product 4">
                <h3>Hair Wax</h3>
                <p class="content">ClassyCut's provides hair wax delivers a strong, lexible hold with, matte texture for all-day style.</p>
                <p>₹ 699 <i> ( 50g )</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/conditioner.jpg" alt="Product 2">
                <h3>Hair Conditioner</h3>
                <p class="content">classycut's hair conditioner is smooths, detangles and leaving it soft and shiny.</p>
                <p>₹ 199 <i> ( 100ml )</i></p>
                <button>Add To Cart</button>
            </div>
            
            <div class="product-card">
                <img src="products/shampoo.png" alt="Product 4">
                <h3>Hair Shampoo</h3>
                <p class="content">ClassyCut's shampoo deeply cleanses and hydrates for soft, healthy, and manageable hair.</p>
                <p>₹ 399 <i> (100ml)</i></p>
                <button>Add To Cart</button>
            </div>
            
            <div class="product-card">
                <img src="products/serum.jpg" alt="Product 4">
                <h3>Hair Serum</h3>
                <p class="content">ClassyCut's Hair Serum  a lightweight, shine, and protects your hair from heat and damage.</p>
                <p>₹ 499 <i> (50ml)</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/hairjel.jpg" alt="Product 4">
                <h3>Hair gel</h3>
                <p class="content">ClassyCut's provides hair gel offers firm control and a smooth, residue-free shine for any style.</p>
                <p>₹ 249 <i> (50g)</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/facewash.jpg" alt="Product 2">
                <h3>Face Wash</h3>
                <p class="content">ClassyCut's Face Wash gently cleanses and balances your skin, removing impurities for a refreshed and glow.</p>
                <p>₹ 499 <i> (100ml)</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/facecream.jpg" alt="Product 4">
                <h3>Face Cream</h3>
                <p class="content">ClassyCut's hydrating face cream deeply moisturizes and rejuvenates skin for a radiant, youthful glow.</p>
                <p>₹ 199 <i> (100ml)</i></p>
                <button>Add To Cart</button>
            </div>
        
            <div class="product-card">
                <img src="products/beardoil2.jpg" alt="Product 4">
                <h3>Beard Oil</h3>
                <p class="content">ClassyCut's beard oil conditions and softens for a well-groomed, smooth beard with a subtle shine.</p>
                <p>₹ 499 <i> ( 100ml )</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/beardcream.jpg" alt="Product 4">
                <h3>Beard Cream</h3>
                <p class="content">
                    ClassyCut's beard cream tames and hydrates your beard, ensuring a smooth, polished look with every use.
                </p>
                <p>₹ 799 <i> ( 100g )</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/goldmask.jpg" alt="Product 4">
                <h3>Golden Face Mask</h3>
                <p class="content">ClassyCut's Gold Mask delivers a golden touch of luxury, illuminating your skin for a radiant glow.</p>
                <p>₹ 1999 <i> ( 50g )</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/silvermask.jpg" alt="Product 4">
                <h3>Silver Face Mask</h3>
                <p class="content">ClassyCut's Silver Mask revitalizes your skin with a premium silver formula for a luminous, sophisticated glow.</p>
                <p>₹ 1499 <i> ( 50g )</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/charcolmask.jpg" alt="Product 4">
                <h3>Charcol Face Mask</h3>
                <p class="content">ClassyCut's Charcoal Facial Mask detoxifies and purifies for a clear and refreshed complexion.</p>
                <p>₹ 999 <i> ( 50g )</i></p>
                <button>Add To Cart</button>
            </div>

            <div class="product-card">
                <img src="products/vitaminmask.jpg" alt="Product 4">
                <h3>Vitamin-c Face Mask</h3>
                <p class="content">ClassyCut's Vitamin C Face mask brightens and energizes your skin, revealing a radiant and youthful complexion.</p>
                <p>₹ 599 <i> ( 50g )</i></p>
                <button>Add To Cart</button>
            </div> -->

            <!-- display product -->
            <?php
            while ($row = mysqli_fetch_assoc($all_product)) {
                $original_price = (float) $row["p_price"];
                $discount = isset($row["p_discount"]) ? (float) $row["p_discount"] : 0;
                $discounted_price = $original_price - (($original_price * $discount) / 100);
                $filter_price = $discount > 0 ? $discounted_price : $original_price;
                $filter_name = strtolower(trim((string) $row["p_name"]));
                $filter_desc = strtolower(trim((string) $row["p_desc"]));
                $filter_size = trim((string) $row["p_size"]);
                $filter_blob = trim($filter_name . " " . $filter_desc . " " . strtolower($filter_size));
            ?>
                <div class="product-card"
                    data-product-card="true"
                    data-name="<?php echo htmlspecialchars($filter_name, ENT_QUOTES); ?>"
                    data-search="<?php echo htmlspecialchars($filter_blob, ENT_QUOTES); ?>"
                    data-size="<?php echo htmlspecialchars($filter_size, ENT_QUOTES); ?>"
                    data-price="<?php echo number_format($filter_price, 2, '.', ''); ?>">
                    <img src="upload_product_photos/<?php echo $row["p_img"]; ?>" alt="Product 4">
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
            No products match your filters. Try resetting or increasing your price range.
        </div>
        <h1><i class="fas fa-lock"></i> Unlock Premium Combos</h1>
        <p> "Unlock the ultimate value and elevate your experience with our premium combo, offering unbeatable quality and luxury in one exclusive package."</p>

        <div class="product-container">
            <?php if (!$combo_table_ready || !$combo_products_table_ready): ?>
                <div class="combo-empty-note">
                    Premium combos are not available yet. Run <code>admin/setup_combo_management.php</code> once from admin.
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
                    No premium combos available right now. Please check back soon.
                </div>
            <?php endif; ?>
        </div>
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

            const cards = Array.from(grid.querySelectorAll('[data-product-card="true"]'));
            if (!cards.length) return;

            const searchInput = document.getElementById('eshopSearchInput');
            const sortSelect = document.getElementById('eshopSortSelect');
            const maxPriceInput = document.getElementById('eshopPriceMax');
            const priceRange = document.getElementById('eshopPriceRange');
            const sizeRail = document.getElementById('eshopSizeFilters');
            const resultCount = document.getElementById('eshopResultCount');
            const rangeLabel = document.getElementById('eshopRangeLabel');
            const resetButton = document.getElementById('eshopResetBtn');
            const emptyState = document.getElementById('eshopEmptyState');

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

                let visibleCount = 0;
                cards.forEach(function(card) {
                    const searchableText = (card.dataset.search || '').toLowerCase();
                    const sizeText = (card.dataset.size || '').toLowerCase();
                    const price = parseFloat(card.dataset.price || '0');

                    const matchesSearch = !searchTerm || searchableText.includes(searchTerm);
                    const matchesSize = activeSize === 'all' || sizeText === activeSize;
                    const matchesPrice = price <= currentMaxPrice;
                    const shouldShow = matchesSearch && matchesSize && matchesPrice;

                    card.style.display = shouldShow ? '' : 'none';
                    if (shouldShow) visibleCount++;
                });

                resultCount.textContent = visibleCount + (visibleCount === 1 ? ' product found' : ' products found');
                emptyState.hidden = visibleCount !== 0;
            };

            const runFilters = function() {
                sortCards(sortSelect.value);
                applyFilters();
            };

            sizeRail.addEventListener('click', function(event) {
                const target = event.target.closest('.eshop-size-chip');
                if (!target) return;
                activateChip(target);
                runFilters();
            });

            searchInput.addEventListener('input', applyFilters);
            sortSelect.addEventListener('change', runFilters);
            maxPriceInput.addEventListener('input', applyFilters);

            priceRange.addEventListener('input', function() {
                maxPriceInput.value = priceRange.value;
                applyFilters();
            });

            resetButton.addEventListener('click', function() {
                searchInput.value = '';
                sortSelect.value = 'relevance';
                maxPriceInput.value = String(maxPriceLimit);
                priceRange.value = String(maxPriceLimit);
                const allChip = sizeRail.querySelector('[data-size="all"]');
                if (allChip) activateChip(allChip);
                runFilters();
            });

            runFilters();
        });
    </script>
    <script src="js/script.js"></script>
</body>

</html>
