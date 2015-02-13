<?php
  require_once(dirname(__FILE__) . '/../config.php');
  require_login();
  require_capability('moodle/site:doanything', get_context_instance(CONTEXT_SYSTEM));
  print_header();
  echo '&lt;p>Rebuilding context paths ...&lt;/p>';
  build_context_path(true);
  echo '&lt;p>Done&lt;/p>';
  print_footer('empty');
?>