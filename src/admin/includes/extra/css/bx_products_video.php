<?php
/** ------------------------------------------------------------------------------------
 * $Id: admin/includes/extra/css/bx_products_video.php 1000 2025-06-01 00:00:00Z benax $
 * 
 * ██████╗ ███████╗███╗   ██╗ █████╗ ██╗  ██╗
 * ██╔══██╗██╔════╝████╗  ██║██╔══██╗╚██╗██╔╝
 * ██████╔╝█████╗  ██╔██╗ ██║███████║ ╚███╔╝ 
 * ██╔══██╗██╔══╝  ██║╚██╗██║██╔══██║ ██╔██╗ 
 * ██████╔╝███████╗██║ ╚████║██║  ██║██╔╝ ██╗
 * ╚═════╝ ╚══════╝╚═╝  ╚═══╝╚═╝  ╚═╝╚═╝  ╚═╝
 * 
 * modified eCommerce Shopsoftware
 * http://www.modified-shop.org
 * 
 * Copyright (c) 2009 - 2025 [www.modified-shop.org]
 * -------------------------------------------------------------------------------------
 * Released under the GNU General Public License
 * -------------------------------------------------------------------------------------
 */

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
  if (defined('MODULE_BX_PRODUCTS_VIDEO_STATUS') && MODULE_BX_PRODUCTS_VIDEO_STATUS == 'true' && basename($_SERVER['PHP_SELF']) == 'bx_products_video.php') {
?>
  <style>
    #systemMessage {
      display: none;
      border: 1px solid grey;
      padding: 1rem;
      font-weight: bold;
    }
    .message-warning {
      background-color: crimson; 
      color: white;
    }
    .message-success {
      background-color: #669933; 
      color: white;
    }
  </style>
<?php
  }
?>