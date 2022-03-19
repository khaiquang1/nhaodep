<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>jQuery FileUp Demos</title>
<link href="jquery.growl.css" rel="stylesheet" type="text/css">
<link href="src/fileup.css" rel="stylesheet" type="text/css">
<style>
    body { background-color:#fafafa; font-family:'Roboto';}
    h2 { margin:20px auto;}
    .container { margin:50px auto;}
    .dropzone {
        background-color: #ccc;
        border: 3px dashed #888;
        width: 350px;
        height: 150px;
        border-radius: 25px;
        font-size: 20px;
        color: #777;
        padding-top: 50px;
        text-align: center;
    }
    .dropzone.over {
        opacity: .7;
        border-style: solid;
    }
    #dropzone .dropzone {
        margin-top: 25px;
        padding-top: 60px;
    }
</style>
</head>
<body>
  <div id="upload-1-queue" class="queue"></div>
    <form id="multiple" enctype="multipart/form-data">
        <input type="file" multiple="true" id="photos" name="photos[]" accept=".png,.jpg,.gif">
<button name="nam" value="upload" onclick="addLog()">Upload</button>
    </form>

</div>
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="jquery.growl.js"></script>
<script src="src/fileup.js"></script>
<script>
function addLog() {
      $photos=$("#photos").val();
      //{'logs': $title, 'tags': $tags, 'lead_id': $lead_id, 'logs_description': $description, 'photos': $photos, _token: 'qCMDrV751IkasHjfXPcSunoqJ3ZxsPaTGk54Pfzk'}
          $.ajax({
              type: "post",
              url: 'https://fastercrm.com/imgupload/upload.php?file_upload=1',
              enctype: 'multipart/form-data',
              data:{'photos': $photos, _token: 'qCMDrV751IkasHjfXPcSunoqJ3ZxsPaTGk54Pfzk'},
              success: function (data) {
                  alert("Cài đặt thành công");
                  $.magnificPopup.close();
                  loadHistory($lead_id);
              }
          });
  }
 
        $.fileup({
            url: 'https://fastercrm.com/imgupload/upload.php?file_upload=1',
            inputID: 'upload-2',
            dropzoneID: 'upload-2-dropzone',
            queueID: 'upload-2-queue',
            onSelect: function(file) {
                $('#multiple .control-button').show();
            },
            onRemove: function(file, total) {
                if (file === '*' || total === 1) {
                    $('#multiple .control-button').hide();
                }
            },
            onSuccess: function(response, file_number, file) {
                 $( "#listPhoto" ).append( "<input type='hidden' name='photos[]' value='"+response+"' />");

            },
            onError: function(event, file, file_number) {
                $.growl.error({ message: "Upload error!" });
            }
        });

    </script>
</body>
</html>
