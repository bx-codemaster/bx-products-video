<?php
/** ------------------------------------------------------------------------------------------
 * $Id: admin/includes/modules/system/bx_products_video.php 1000 2025-06-01 00:00:00Z benax $
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
 * -------------------------------------------------------------------------------------------
 * Released under the GNU General Public License
 * -------------------------------------------------------------------------------------------
 */

   defined( '_VALID_XTC' ) or die( 'Direct Access to this location is not allowed.' );

   class bx_products_video {
     public string $code;
     public string $version;
     public string $development_status;
     public string $title;
     public string $description;
     public int $sort_order;
     public bool $enabled;
     public int $_check;
   
     public function __construct() {
        $this->code        = 'bx_products_video';
        $this->version     = '2.0.5';
        $this->title       = defined('MODULE_BX_PRODUCTS_VIDEO_TEXT_TITLE') ? MODULE_BX_PRODUCTS_VIDEO_TEXT_TITLE : '';
        $this->description = defined('MODULE_BX_PRODUCTS_VIDEO_TEXT_DESCRIPTION') ? MODULE_BX_PRODUCTS_VIDEO_TEXT_DESCRIPTION : '';
        $this->sort_order  = defined('MODULE_BX_PRODUCTS_VIDEO_SORT_ORDER') ? MODULE_BX_PRODUCTS_VIDEO_SORT_ORDER : 0;
        $this->enabled     = ((defined('MODULE_BX_PRODUCTS_VIDEO_STATUS') && MODULE_BX_PRODUCTS_VIDEO_STATUS == 'true') ? true : false);
        $this->development_status = 'p';
      }
   
     public function process($file): void {
     }
   
     public function display(): array {
       return array('text' => '<div style="text-align: center;">'.xtc_button(BUTTON_SAVE).xtc_button_link(BUTTON_CANCEL, xtc_href_link(FILENAME_MODULE_EXPORT, 'set='.$_GET['set'].'&module='.$this->code))."</div>");
     }
   
     public function check(): int {
       if (!isset($this->_check)) {
         $check_query = xtc_db_query("SELECT configuration_value 
                                        FROM ".TABLE_CONFIGURATION."
                                       WHERE configuration_key = 'MODULE_BX_PRODUCTS_VIDEO_STATUS'");
         $this->_check = xtc_db_num_rows($check_query);
       }
       return $this->_check;
     }

     public function update() {
     }
         
     public function install(): void {
       xtc_db_query("ALTER TABLE ".TABLE_PRODUCTS." ADD products_video VARCHAR(255) NOT NULL AFTER products_image;");
       xtc_db_query("ALTER TABLE ".TABLE_PRODUCTS." ADD KEY idx_products_video (products_video);");
       xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." ADD bx_products_video INTEGER(1)");
       xtc_db_query("UPDATE ".TABLE_ADMIN_ACCESS." SET bx_products_video = 1");
   
       $freeId_query = xtc_db_query("SELECT (configuration_group_id+1) AS id 
                                       FROM " . TABLE_CONFIGURATION . " 
                                      WHERE (configuration_group_id+1) NOT IN (SELECT configuration_group_id FROM " . TABLE_CONFIGURATION . ") 
                                      LIMIT 1;");
       $freeId = xtc_db_fetch_array($freeId_query);
       
       xtc_db_query("INSERT INTO ".TABLE_CONFIGURATION." ( configuration_id, 
                                                           configuration_key, 
                                                           configuration_value, 
                                                           configuration_group_id, 
                                                           sort_order, 
                                                           set_function, 
                                                           date_added ) 
                                                  VALUES ( '', 
                                                           'MODULE_BX_PRODUCTS_VIDEO_STATUS', 
                                                           'true',  
                                                           '".$freeId["id"]."', 
                                                           '1', 
                                                           'xtc_cfg_select_option(array(\'true\', \'false\'), ',
                                                           now() );");
     }
   
     public function remove(): void {
       xtc_db_query("DROP INDEX idx_products_video ON ".TABLE_CONFIGURATION.";");
       xtc_db_query("ALTER TABLE ".TABLE_PRODUCTS." DROP products_video;");
       xtc_db_query("ALTER TABLE ".TABLE_ADMIN_ACCESS." DROP bx_products_video;");
       xtc_db_query("DELETE FROM ".TABLE_CONFIGURATION." WHERE configuration_key in ('".implode("', '", $this->keys())."')");
     } 
   
     public function keys(): array {
       $key = array('MODULE_BX_PRODUCTS_VIDEO_STATUS');
       return $key;
     }
   }
