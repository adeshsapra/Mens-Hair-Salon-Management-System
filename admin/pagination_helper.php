<?php
/**
 * Reusable Pagination Function
 * 
 * @param int $total_records Total number of records in the database
 * @param int $current_page The current page number
 * @param int $records_per_page Number of records to display per page
 * @param string $page_url The base URL for pagination links (e.g., 'customer.php')
 * @param array $extra_params Extra query params to preserve (e.g., ['tab' => 'haircut'])
 * @param string $page_param Query param name for page (default: page)
 * @return string HTML for the pagination components
 */
function renderPagination($total_records, $current_page, $records_per_page = 10, $page_url = '', $extra_params = [], $page_param = 'page') {
    $total_pages = ceil($total_records / $records_per_page);
    
    if ($total_pages <= 1) return '';

    $buildPageUrl = function($page) use ($page_url, $extra_params, $page_param) {
        $params = $extra_params;
        $params[$page_param] = $page;
        return $page_url . '?' . http_build_query($params);
    };

    $html = '<div class="pagination-container">';

    // Previous Button
    if ($current_page > 1) {
        $html .= '<a href="' . $buildPageUrl($current_page - 1) . '" class="pagination-item"><i class="fas fa-chevron-left"></i></a>';
    } else {
        $html .= '<span class="pagination-item disabled"><i class="fas fa-chevron-left"></i></span>';
    }

    // Page Numbers
    // Showing a maximum of 5 page links around the current page
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $start_page + 4);
    if ($end_page - $start_page < 4) {
        $start_page = max(1, $end_page - 4);
    }

    for ($i = $start_page; $i <= $end_page; $i++) {
        $active_class = ($i == $current_page) ? 'active' : '';
        $html .= '<a href="' . $buildPageUrl($i) . '" class="pagination-item ' . $active_class . '">' . $i . '</a>';
    }

    // Next Button
    if ($current_page < $total_pages) {
        $html .= '<a href="' . $buildPageUrl($current_page + 1) . '" class="pagination-item"><i class="fas fa-chevron-right"></i></a>';
    } else {
        $html .= '<span class="pagination-item disabled"><i class="fas fa-chevron-right"></i></span>';
    }

    $html .= '</div>';

    return $html;
}
?>
