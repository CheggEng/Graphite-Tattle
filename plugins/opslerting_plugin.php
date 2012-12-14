<? 

plugin_listener('plugin_settings','opslert_plugin_settings');
plugin_listener('send_methods','opslert_plugin_send_methods');
plugin_listener('plugin_user_settings','opslert_plugin_user_settings');

// Poor Mans Module Settings
function opslert_plugin_settings(){
  return array( 
              'ops_alert_server' => array('friendly_name' => 'Ops Alert Server', 
                                     'default' => 'alerts.cheggnet.com',
                                     'type' => 'string'),
              'ops_alert_server_offset' => array('friendly_name' => 'Alert Offset',
                                   'default' => 1000000, 
                                   'type' => 'integer')
              
              );
}

function opslert_plugin_user_settings() {
  return array(
              'warning_monitor_id' => array('friendly_name' => 'Warning Ops Monitor Id',
                                   'default' => 0,
                                   'type' => 'integer'),
              'emergency_monitor_id' => array('friendly_name' => 'Emergency Ops Monitor Id',
                                   'default' => 0,
                                   'type' => 'integer')
             );
}
function opslert_plugin_send_methods(){
  return array('opslert_plugin_notify_warning' => 'Ops Warning','opslert_plugin_notify_emergency' => 'Ops Emergency');
}

function opslert_plugin_notify_warning($check,$check_result,$subscription) {
	return opslert_plugin_notify($check,$check_result,$subscription,0);
}
function opslert_plugin_notify_emergency($check,$check_result,$subscription) {
	return opslert_plugin_notify($check,$check_result,$subscription,1);
}


//opslert plugin
function opslert_plugin_notify($check,$check_result,$subscription,$alt_opslert) {
  global $status_array;
  $user = new User($subscription->getUserId());
  
  $state = $status_array[$check_result->getStatus()];
  
  $alert_baseurl = "http://" . sys_var('ops_alert_server') . "/cgi-bin/alert";
  if ($alt_opslert)
  	$alert_id = usr_var('emergency_monitor_id',$user->getUserId());
  else
  	$alert_id = usr_var('warning_monitor_id',$user->getUserId());
  $alert_object = urlencode($check->prepareName());
  $alert_link = urlencode($GLOBALS['TATTLE_DOMAIN'] . '/' . CheckResult::makeURL('list',$check_result));
  $alert_host = urlencode($GLOBALS['TATTLE_DOMAIN'] .':' . $user->getEmail());

  $state_email_injection = $state . " Alert ";
  if($state == 'OK') {
    $state_email_injection = "Everything's back to normal ";
  }
  $check_type = '';
  if($check->getType() == 'threshold') {
    $check_type = ' Threshold';
  } elseif($check->getType() == 'predictive') {
    $check_type = ' Standard Deviation';
  }
  $alert_note =  urlencode("<p>" . $state_email_injection . "for {$check->prepareName()} </p><p>The check returned {$check_result->prepareValue()}</p><p>Warning" . $check_type  . " is : ". $check->getWarn() . "</p><p>Error" . $check_type . " is : ". $check->getError() . '</p><p>View Alert Details : <a href="' . $GLOBALS['TATTLE_DOMAIN'] . '/' . CheckResult::makeURL('list',$check_result) . '">'.$check->prepareName()."</a></p>");
  $alert_url = "$alert_baseurl?id=$alert_id&object=$alert_object&link=$alert_link&host=$alert_host&note=$alert_note";


	$response = http_get($alert_url, array("timeout"=>10), $info);
	log_action("opslerting to $alert_url\nResult: $response");

}
