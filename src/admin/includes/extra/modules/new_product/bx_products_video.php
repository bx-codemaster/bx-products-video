<?php
/** ----------------------------------------------------------------------------------------------------
 * $Id: admin/includes/extra/modules/new_product/bx_products_video.php 1000 2025-06-01 00:00:00Z benax $
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
 * -----------------------------------------------------------------------------------------------------
 * Released under the GNU General Public License
 * -----------------------------------------------------------------------------------------------------
 * BESCHREIBUNG:
 * Frontend-Interface für Video-Upload im Produktverwaltungs-Bereich.
 * Bietet eine benutzerfreundliche Oberfläche für chunk-basierte Video-Uploads.
 * 
 * FUNKTIONALITÄT:
 * - Drag & Drop Video-Upload Interface
 * - Chunk-basierter Upload mit Fortschrittsanzeige
 * - Live-Vorschau hochgeladener Videos
 * - Video löschen und ersetzen
 * - Automatic File-Input Detection mit Fallbacks
 * 
 * CLIENTSEITIGE VALIDIERUNG:
 * - Format-Whitelist (MP4, WebM, Ogg)
 * - MIME-Type und Extension Prüfung
 * - Dateigröße-Limit: 100 MB
 * - Echtzeit-Fehlerbehandlung mit Benutzer-Feedback
 * 
 * JAVASCRIPT FUNKTIONEN:
 * - UploadVideo(): Chunk-basierter Upload Handler
 * - isValidVideoFormat(): Format-Validierung
 * - formatBytes(): Dateigröße-Formatierung
 * - systemMessage(): Benutzer-Benachrichtigungen
 * 
 * INTEGRATION:
 * Wird automatisch in die Produktbearbeitungsseite (categories.php) eingebunden.
 * 
 * ----------------------------------------------------------------------------------------------------
 * Released under the GNU General Public License
 * ----------------------------------------------------------------------------------------------------
 */

  defined('_VALID_XTC') or die('Direct Access to this location is not allowed.');
  
  $http_server = (ENABLE_SSL || $request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER;
  if(!defined("DIR_WS_MEDIA_VIDEOS") ) {
    define('DIR_WS_MEDIA_VIDEOS', DIR_WS_CATALOG.'media/products/');
  }
 // MP4 (H.264), WebM (VP8/VP9), Ogg (Theora)
  $currVideo     = '';
  $mimetype      = 'mp4';
  $videoNotEmpty = 'none';
  $videoEmpty    = 'table';

  $productsVideo = isset($pInfo->products_video) ? $pInfo->products_video : "";
  $productsId    = isset($pInfo->products_id) ? $pInfo->products_id : "";

  if( !empty($productsVideo) ) {
    $currVideo     = $http_server.DIR_WS_MEDIA_VIDEOS.$productsVideo;
    $mimetype      = pathinfo($currVideo, PATHINFO_EXTENSION);
    $videoNotEmpty = 'table';
    $videoEmpty    = 'none';
  }
?>
    <div id="videoMover" style="display:block; margin: 0; padding:0;">
      <div style="clear:both;"></div>
      <div class="main div_header"><strong><?php echo HEADING_PRODUCTS_VIDEO; ?>:</strong></div>
      <input id="saveProductsVideo" type="hidden" name="products_video" value="<?php echo $productsVideo; ?>">
      <div class="clear div_box">
        <p id="systemMessage"></p>
        <table id="videoNotEmpty" class="tableConfig borderall" style="margin-top: 0; display: <?php echo $videoNotEmpty; ?>;">
          <tbody>
            <tr>
              <td class="dataTableConfig col-left" style="vertical-align: top;"><p class="main"><?php echo HEADING_FILE_NAME; ?>:</p></td>
              <td class="dataTableConfig col-middle" style="vertical-align: top;">
                <a id="deleteVideo" class="button" href="javascript:void(0);" style="margin:0; text-decoration: none; float:right;"><?php echo TEXT_DELETE; ?></a>
                <p class="main" id="videoName"><strong><?php echo $productsVideo; ?></strong> <span class="main" id="videoSize"><strong>&nbsp;</strong></span> <span class="main" id="videoType"><strong>&nbsp;</strong></span></p>
              </td>
              <td class="dataTableConfig col-right">
                <div id="videoWrapper" style="text-align: left;">
                  <video style="max-height: 200px; margin: auto;" controls loop>
                    <source id="videoSource" src="<?php echo $currVideo; ?>" type="video/<?php echo $mimetype; ?>">
                  </video>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <table id="videoEmpty" class="tableConfig borderall" style="margin-top: 0; display: <?php echo $videoEmpty; ?>;">
          <tbody>
              <tr>
                <td class="dataTableConfig col-left"><?php echo HEADING_FILE_NAME; ?>:</td>
                <td class="dataTableConfig col-middle">
                  <span class="main" id="videoNameE"></span> <span class="main" id="videoSizeE"></span> <span class="main" id="videoTypeE"></span>
                </td>
                <td class="dataTableConfig col-right" rowspan="3">
                  <div id="videoWrapperE" style="text-align: left; display: none;">
                  <video style="max-height: 200px; margin: auto;" controls>
                    <source id="videoSourceE" src="" type="video/<?php echo $mimetype; ?>">
                  </video>
                  <div id="uploadVideoProgressBarE" style="display:none; height: 10px; width: 0%; background: #2781e9;"></div>
                </div>
              </td>
            </tr>
            <tr>
              <td class="dataTableConfig col-left"><?php echo TEXT_VIDEO_FILE; ?>:</td>
              <td class="dataTableConfig col-middle txta-r">
                <?php echo xtc_draw_file_field('products_video', 'accept="video/mp4,video/webm,video/ogg"'); ?>
              </td>
            </tr>
            <tr>
              <td class="dataTableConfig col-left">Upload</td>
              <td class="dataTableConfig col-middle txta-r">
                <a id="uploadVideoE" class="button" href="javascript:void(0);" style="margin:0; text-decoration: none;"><?php echo TEXT_UPLOAD; ?></a>
              </td>
            </tr>
          </tbody>
        </table>

      </div>
    </div>
      <script>
  $(function() {
    "use strict";

    /* Eingabefelder dynamisch verschieben */
    $("#videoMover").insertBefore($("input#finput_products_image").closest("table").closest("div.div_box").prev("div.div_header"));

    // Robuste Element-Suche mit Fallbacks
    var fileInput = document.getElementById("products_video") || 
                    document.querySelector('input[name="products_video"]') ||
                    document.getElementById("finput_products_video");
    
    $("#uploadVideoE").on('click', function(event) {
      event.preventDefault();
      
      // Validierung vor Upload
      if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        systemMessage("Bitte wählen Sie zuerst eine Videodatei aus", "message-error");
        return false;
      }
      
      UploadVideo(fileInput.files[0]);
    });

    // Event auf Input-Element mit robustem Selector
    $('input[name="products_video"]').on("change", function() {
      fileInput = this; // Update fileInput Referenz
      
      if ('files' in fileInput) {
        if (fileInput.files.length === 0) {
          alert("Wählen Sie eine Videodatei aus");
        } else {
          // FORMAT-VALIDIERUNG HINZUFÜGEN
          if (!isValidVideoFormat(fileInput.files[0])) {
            systemMessage("Nur MP4, WebM und Ogg Videoformate sind erlaubt!", "message-error");
            $(this).val(''); // Input zurücksetzen
            return false;
          }

          let $source = $('#videoSourceE');
          $source[0].src = URL.createObjectURL(fileInput.files[0]);
          $source.parent()[0].load();

          // Berechne finalen Server-Dateinamen
          let fileExtension = fileInput.files[0].type.substring(fileInput.files[0].type.indexOf('/')+1);
          let finalFilename = 'video-<?php echo str_pad($productsId, 4, "0", STR_PAD_LEFT); ?>.' + fileExtension;
          
          $("#videoNameE").html(finalFilename + ' <small style="color:#666;">(Original: ' + fileInput.files[0].name + ')</small>');
          $("#saveProductsVideo").val(finalFilename);
          $("#videoSizeE").html( '('+formatBytes(fileInput.files[0].size)+', ' );
          $("#videoTypeE").html(fileInput.files[0].type+')');
          $("#videoWrapperE").fadeIn("slow");
        }
      } else {
        //console.log('No found "files" property');
      }
    });

    $("#deleteVideo").on('click', function(event) {
      event.preventDefault();

      let filename = '<?php echo $productsVideo; ?>';
      if('' === filename) {
        filename = $("#videoName").text();
      }
      
      $.ajax({
        url: "<?php echo xtc_href_link('bx_products_video.php', 'action=delete'); ?>",
        type: "POST",
        data: { bxName: filename, pId: '<?php echo $productsId; ?>'<?php if (defined('CSRF_TOKEN_SYSTEM') && CSRF_TOKEN_SYSTEM == 'true') { echo ', '.$_SESSION["CSRFName"].": '".$_SESSION["CSRFToken"]."'"; } ?>}})
        .done(function (data) {
        $("#videoNotEmpty").slideUp("slow", function() {
          $("#videoWrapperE").hide("slow");
          $("#videoNameE").html("&nbsp;");
          $("#videoSizeE").html("&nbsp;");
          $("#videoTypeE").html("&nbsp;");
          let $sourceE = $('#videoSourceE');
          $sourceE[0].src = "";
          $sourceE.parent()[0].load();

          let $source = $('#videoSource');
          $source[0].src = "";
          $source.parent()[0].load();

          $("#videoName").html("&nbsp;");
          $("#videoSize").html("&nbsp;");
          $("#videoType").html("&nbsp;");
          $("#videoSource").attr("src","");
          $('input[name="products_video"]').val("");
          $("#saveProductsVideo").val("");

          $("#videoEmpty").slideDown("slow");
          systemMessage("<?php echo TEXT_VIDEO_DELETED; ?>", "message-success");
        });
      });
    });

    function UploadVideo(file) {
      //console.log(file);
      if (!file) {
        systemMessage("Keine Datei zum Hochladen ausgewählt", "message-error");
        return false;
      }
      
      // Starte Upload direkt (überschreibt existierende Datei)
      let loaded    = 0;
      let chunkSize = 500000;
      let total     = file.size;
      let reader    = new FileReader();
      let slice     = file.slice(0, chunkSize);
        
      // Reading a chunk to invoke the 'onload' event
      reader.readAsDataURL(slice);
      console.log('Started uploading file "' + file.name + '"');            
      $('#uploadVideoProgressBarE').show();
      
      reader.onload = function (event) {
          //Send the sliced chunk to the REST API
          var fd = new FormData();
          <?php if (defined('CSRF_TOKEN_SYSTEM') && CSRF_TOKEN_SYSTEM == 'true') {
            echo "fd.append('".$_SESSION["CSRFName"]."', '".$_SESSION["CSRFToken"]."');";
          } ?>          
          fd.append('pId', '<?php echo $productsId; ?>');
          fd.append('data', event.target.result);
          
          // Beim ersten Chunk: Markiere als ersten Upload
          let uploadUrl = "<?php echo xtc_href_link('bx_products_video.php', 'action=upload&bxName='); ?>"+ file.name;
          if (loaded === 0) {
            uploadUrl += "&first=1";
          }
          
          $.ajax({
            url: uploadUrl,
            type: "POST",
            data: fd,
            processData: false,
            contentType: false,
            error: (function (xhr, status, errorData) {
              console.error('Upload Error:', xhr.responseText);
              let errorMsg = "Video Upload fehlgeschlagen";
              try {
                const response = JSON.parse(xhr.responseText);
                if (response.error) {
                  errorMsg = response.error;
                }
              } catch(e) {
                // Fallback
              }
              systemMessage(errorMsg, "message-error");
              $('#uploadVideoProgressBarE').hide();
            })
          }).done(function (data) {
            console.log('Chunk uploaded:', data);
            loaded += chunkSize;
            let percentLoaded = Math.min((loaded / total) * 100, 100);
            console.log('Uploaded ' + Math.floor(percentLoaded) + '% of file "' + file.name + '"');
            $('#uploadVideoProgressBarE').width(percentLoaded + "%");
            //Read the next chunk and call 'onload' event again
            if (loaded <= total) {
              slice = file.slice(loaded, loaded + chunkSize);
              reader.readAsDataURL(slice);
            } else { 
              loaded = total;
              //console.log(file);
              let $filename = 'video-<?php echo str_pad($productsId, 4, "0", STR_PAD_LEFT); ?>.'+file.type.substring(file.type.indexOf('/')+1);
              //console.log($filename);
              $.ajax({
                url: "<?php echo xtc_href_link('bx_products_video.php', 'action=update'); ?>",
                type: "POST",
                data: { bxName: $filename, pId: '<?php echo $productsId; ?>'<?php if (defined('CSRF_TOKEN_SYSTEM') && CSRF_TOKEN_SYSTEM == 'true') { echo ', '.$_SESSION["CSRFName"].": '".$_SESSION["CSRFToken"]."'"; } ?> },
                error: function(xhr, status, error) {
                  console.error('Update Error:', xhr.responseText);
                  let errorMsg = "Video-Validierung fehlgeschlagen";
                  try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.error) {
                      errorMsg = response.error;
                    }
                  } catch(e) {
                    // Fallback
                  }
                  systemMessage(errorMsg, "message-error");
                  $('#uploadVideoProgressBarE').hide();
                }
              }).done( function (data) {
                $('#uploadVideoProgressBarE').hide();

                //let $filename = 'video-<?php echo str_pad($productsId, 4, "0", STR_PAD_LEFT); ?>.'+file.type.substring(file.type.indexOf('/')+1);
                // Setze Video-Source auf finale Server-Datei
                let $source = $('#videoSource');
                $source.attr("src", "<?php echo $http_server.DIR_WS_MEDIA_VIDEOS; ?>"+$filename);
                $source.parent()[0].load();
                
                $("#videoName").html($filename);
                $("#videoSize").html( '('+formatBytes(file.size)+', ' );
                $("#videoType").html(file.type+')');
                $("#saveProductsVideo").val($filename);

                $("#videoEmpty").slideUp("slow", function() {
                  systemMessage("Upload erfolgreich beendet!", "message-success");
                  $("#videoNotEmpty").slideDown("slow");
                });
              });
            }
          })
        };
    }

    function formatBytes(a, b=2) {
      if(!+a) {
        return"0 Bytes";
      }
      const c=0>b?0:b,d=Math.floor(Math.log(a)/Math.log(1024));
      return`${parseFloat((a/Math.pow(1024,d)).toFixed(c))} ${["Bytes","KB","MB","GB","TB","PB","EB","ZB","YB"][d]}`;
    }

    function systemMessage (text = '', klasse = '') {
      $('#systemMessage').removeClass('message-error message-success message-warning');
      return $('#systemMessage').addClass(klasse).html(text).slideDown("slow", function() {
        setTimeout(function(){ 
          $("#systemMessage").slideUp("slow", function() {
            $(this).removeClass('message-error message-success message-warning');
          }); 
        }, 5000);
      });
    }
  
    // Validierungsfunktion nach formatBytes() einfügen:
    function isValidVideoFormat(file) {
      const allowedTypes = ['video/mp4', 'video/webm', 'video/ogg'];
      const allowedExtensions = ['.mp4', '.webm', '.ogg', '.ogv'];
      
      const mimeType = file.type.toLowerCase();
      const fileName = file.name.toLowerCase();
      const extension = fileName.substring(fileName.lastIndexOf('.'));
      
      return allowedTypes.includes(mimeType) && allowedExtensions.includes(extension);
    }

  });
</script>
