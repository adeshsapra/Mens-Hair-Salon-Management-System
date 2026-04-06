<?php

/**
 * Filter Helper for Admin Panel
 * Provides reusable filter component and SQL query builder for filters
 */

if (!function_exists('renderFilters')) {
    /**
     * Renders a professional filter bar
     * @param array $filters Array of filter configurations
     * @param string $action Form action URL (default is current page)
     * @param array $hiddenFields Optional key-value pairs for hidden inputs
     */
    function renderFilters($filters, $action = '', $hiddenFields = [])
    {
        if (empty($action)) {
            $action = basename($_SERVER['PHP_SELF']);
        }
?>
        <div class="filter-wrapper">
            <form action="<?php echo htmlspecialchars($action); ?>" method="GET" class="filter-form">
                <?php foreach ($hiddenFields as $name => $val): ?>
                    <input type="hidden" name="<?php echo htmlspecialchars($name); ?>" value="<?php echo htmlspecialchars($val); ?>">
                <?php endforeach; ?>
                <div class="filter-container">
                    <div class="filter-inputs">
                        <?php foreach ($filters as $filter): ?>
                            <div class="filter-item <?php echo isset($filter['class']) ? $filter['class'] : ''; ?>">
                                <?php if (isset($filter['label'])): ?>
                                    <label for="<?php echo $filter['name']; ?>"><?php echo $filter['label']; ?></label>
                                <?php endif; ?>

                                <?php if ($filter['type'] === 'text'): ?>
                                    <div class="filter-input-group">
                                        <i class="fas fa-search"></i>
                                        <input type="text"
                                            name="<?php echo $filter['name']; ?>"
                                            id="<?php echo $filter['name']; ?>"
                                            placeholder="<?php echo $filter['placeholder'] ?? ''; ?>"
                                            value="<?php echo htmlspecialchars($filter['value'] ?? ''); ?>">
                                    </div>

                                <?php elseif ($filter['type'] === 'date'): ?>
                                    <div class="filter-input-group">
                                        <i class="fas fa-calendar-alt"></i>
                                        <input type="date"
                                            name="<?php echo $filter['name']; ?>"
                                            id="<?php echo $filter['name']; ?>"
                                            value="<?php echo htmlspecialchars($filter['value'] ?? ''); ?>">
                                    </div>

                                <?php elseif ($filter['type'] === 'select'): ?>
                                    <div class="filter-input-group">
                                        <i class="fas fa-filter"></i>
                                        <select name="<?php echo $filter['name']; ?>" id="<?php echo $filter['name']; ?>">
                                            <?php foreach ($filter['options'] as $val => $label): ?>
                                                <option value="<?php echo htmlspecialchars($val); ?>" <?php echo (isset($filter['value']) && $filter['value'] == $val) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($label); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="filter-btn apply-btn">
                            <i class="fas fa-sync-alt"></i> Apply Filters
                        </button>
                        <a href="<?php echo htmlspecialchars($action); ?>" class="filter-btn reset-btn">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <style>
            .filter-wrapper {
                background: #fff;
                padding: 25px;
                border-radius: 12px;
                margin-bottom: 30px;
                box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
                border-left: 5px solid var(--bg1);
            }

            .filter-container {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
                align-items: flex-end;
            }

            .filter-inputs {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
                flex: 1 1 0%;
                min-width: 300px;
            }

            .filter-item {
                flex: 1 1 200px;
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            .filter-item label {
                font-size: 13px;
                font-weight: 700;
                color: var(--bg1);
                margin-bottom: 0;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                opacity: 0.8;
            }

            .filter-input-group {
                position: relative;
                display: flex;
                align-items: center;
            }

            .filter-input-group i {
                position: absolute;
                left: 12px;
                color: var(--bg1);
                font-size: 14px;
                pointer-events: none;
                opacity: 0.6;
            }

            .filter-input-group input,
            .filter-input-group select {
                width: 100%;
                padding: 11px 12px 11px 35px;
                border: 2px solid #f0f0f0;
                border-radius: 8px;
                font-size: 14px;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                background: #fdfdfd;
                color: var(--bg1);
                height: 45px;
            }

            .filter-input-group input:focus,
            .filter-input-group select:focus {
                border-color: var(--bg1);
                background: #fff;
                outline: none;
                box-shadow: 0 0 0 4px rgba(24, 21, 13, 0.05);
            }

            .filter-actions {
                display: flex;
                gap: 12px;
                flex-shrink: 0;
                padding-bottom: 2px; /* Alignment tweak */
            }

            .filter-btn {
                height: 45px;
                padding: 0 25px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                text-decoration: none;
                border: none;
                white-space: nowrap;
            }

            .apply-btn {
                background: var(--bg1);
                color: var(--bg2);
                box-shadow: 0 4px 12px rgba(24, 21, 13, 0.15);
            }

            .apply-btn:hover {
                background: #000;
                color: #fff;
                transform: translateY(-2px);
                box-shadow: 0 6px 15px rgba(24, 21, 13, 0.2);
            }

            .reset-btn {
                background: #f5f5f5;
                color: #666;
                border: 1px solid #e0e0e0;
            }

            .reset-btn:hover {
                background: #eeeeee;
                color: var(--bg1);
                border-color: #d0d0d0;
                transform: translateY(-2px);
            }

            @media (max-width: 1300px) {
                .filter-inputs {
                    flex: 1 1 100%;
                }
                .filter-actions {
                    flex: 1 1 100%;
                    justify-content: flex-end;
                }
                .filter-btn {
                    flex: 1;
                }
            }

            @media (max-width: 768px) {
                .filter-item {
                    flex: 1 1 100%;
                }
                .filter-actions {
                    flex-direction: column;
                }
            }
        </style>
<?php
    }
}

if (!function_exists('buildSimpleWhere')) {
    /**
     * Builds a ready-to-use WHERE clause with escaped values
     * @param mysqli $con MySQLi connection
     * @param array $config Configuration mapping GET keys to DB columns
     * @param string $prefix Prefix to use if conditions exist (default ' WHERE ')
     * @return string Escape SQL WHERE clause
     */
    function buildSimpleWhere($con, $config, $prefix = ' WHERE ')
    {
        $where = [];

        foreach ($config as $key => $column) {
            if (isset($_GET[$key]) && $_GET[$key] !== '') {
                $val = mysqli_real_escape_string($con, $_GET[$key]);

                if (is_array($column)) {
                    $type = $column['type'] ?? 'equals';
                    $colName = $column['col'] ?? '';
                    $cols = $column['cols'] ?? [];

                    if ($type === 'like' && $colName) {
                        $where[] = "$colName LIKE '%$val%'";
                    } elseif ($type === 'search' && !empty($cols)) {
                        $searchGroup = [];
                        foreach ($cols as $c) {
                            $searchGroup[] = "$c LIKE '%$val%'";
                        }
                        $where[] = "(" . implode(" OR ", $searchGroup) . ")";
                    } elseif ($type === 'date_start' && $colName) {
                        $where[] = "$colName >= '$val'";
                    } elseif ($type === 'date_end' && $colName) {
                        $where[] = "$colName <= '$val'";
                    } elseif ($type === 'equals' && $colName) {
                        $where[] = "$colName = '$val'";
                    } elseif ($type === 'custom' && isset($column['handler']) && is_callable($column['handler'])) {
                        $customWhere = $column['handler']($con, $val);
                        if (!empty($customWhere)) {
                            $where[] = "($customWhere)";
                        }
                    }
                } else {
                    $where[] = "$column = '$val'";
                }
            }
        }

        if (empty($where)) return "";
        return $prefix . implode(" AND ", $where);
    }
}
