<?
include 'inc/init.php';

fAuthorization::requireLoggedIn();
$breadcrumbs[] = array('name' => 'Alerts', 'url' => 'index.php','active' => false);

$page_num = fRequest::get('page', 'int', 1);
$offset = ($page_num - 1)*$GLOBALS['PAGE_SIZE'];

$results = NULL;

$latest_alerts = 'SELECT c.check_id,name,r.status,count(c.check_id) as count, r.timestamp '.
                    'FROM subscriptions s '.
                    'JOIN checks c ON s.check_id = c.check_id '.
                    'JOIN check_results r ON s.check_id = r.check_id '.
                    'WHERE r.timestamp >= DATE_SUB(CURDATE(),INTERVAL 1 DAY) '.
                    'AND r.status IS NOT NULL '.
                    'AND acknowledged = 0 '.
                    'AND s.user_id = ' . fSession::get('user_id') . ' ' .
                    'GROUP BY c.check_id ' .
                    'LIMIT ' . $GLOBALS['PAGE_SIZE'] . ' ' .
                    'OFFSET ' . $offset . ';';

$results = $mysql_db->query($latest_alerts);
$alert_count = $results->countReturnedRows();



include 'inc/views/index.php';
