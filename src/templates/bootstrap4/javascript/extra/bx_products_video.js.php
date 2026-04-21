<?php
  /* --------------------------------------------------------------
   $Id: bx_products_video.js.php 12435 2023-11-24 09:21:20Z benax $

   modified eCommerce Shopsoftware
   http://www.modified-shop.org

   Copyright (c) 2009 - 2019 [www.modified-shop.org]
   --------------------------------------------------------------
   Released under the GNU General Public License
   --------------------------------------------------------------*/
?>
<script>
  "use strict";

  $('#videoModal').on('shown.bs.modal', function (e) {
		$("#productsVideo")[0].play();
	});

	$('#videoModal').on('hide.bs.modal', function (e) {
		$("#productsVideo")[0].pause();
	});
</script>
