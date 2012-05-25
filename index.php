<?php require_once("header.php"); ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>ztxt 2.0</title>
    
    <link rel="stylesheet" href="css/icons.css" /> 
    <link rel="stylesheet" href="css/modal.css" />
    <link rel="stylesheet" href="css/colorpicker.css" />

    <script src="codemirror/js/codemirror.js" ></script> 
    <script src="codemirror/js/mirrorframe.js" ></script> 
    <script src="js/zen_codemirror.js"></script>
    <script src="js/beautify.js" ></script> 
    
    <script src="js/jquery.min.js"></script>
    <script src="js/colorpicker.js"></script>
    
    <script src="js/z.js"></script>
    <script src="js/dragcols.js"></script>
    <script src="js/filelist.js"></script>
    <script src="js/modals.js"></script>
    <script src="js/codewin.js"></script>
    
    <script>
      $(function(){ 
        
        
        z.editableTypes = /.html$|.htm$|.xml$|.csv$|.txt$|.sql$|.php$|.js$|.json$|css$|.htpasswd$|.htaccess$/;
        
        z.visualTypes = /.html$|.htm$|.php$/;
        
        z.imageTypes = /.jpg$|.jpeg$|.gif$|.png$/;    
        
        // autoTest interval
        z.autoTest;
        
        z.visualPreview = "splash.html";
        
        // node that was clicked in file list
        z.menuNode;
        
        // new node to add to file list
        z.newNode = "";
        
        // is the new document a directory or a file
        z.newFileType = "file";
        
        z.currentPath = "";
        
        // select a few key elements
        z.fileName = $("#fileName");
        z.files = $("#fileList");
        z.topFolder = $("#topFolder");
        z.refresh = $("#refresh");
        z.previewFrame = $("#previewFrame");
        z.docMenu = $("#docMenu");
        z.codeUI = $("#codeUI");
        z.codeBorder = $("#codeBorder");
        z.autoTestCheck = $("#autoTest");
        
        // set up the modals
        z.modal = $("#modal").hide();
        z.modals = new z.Modals();
        
        // hide the code on load
        z.codeBorder.hide();
        
        // utility function to get the last element of a split string/array
        z.splitLast = function(str, exp){
          var n = str.split(exp);
          if (n.length == 1){
            return false;
          }
          return n[n.length - 1];
        };
        
        z.removeLast = function(str, delimeter){
         var arr = str.split(delimeter);
         arr.pop();
         return arr.join(delimeter);
       };
        
        // overlay used with drag and modals
        $("body").append("<div id='overlay'/>");
        z.overlay = $("#overlay");
        z.overlay.css({position : "absolute",
                       backgroundColor : "black",
                       opacity : 0.5,
                       zIndex :100}).hide();
        
        // the code mirror instance
        z.editor = CodeMirror.fromTextArea('code', {
          height: "100%",    
          
          parserfile: ["parsexml.js", "parsecss.js", "tokenizejavascript.js", "parsejavascript.js",
                       "../contrib/php/js/tokenizephp.js", "../contrib/php/js/parsephp.js",
                       "../contrib/php/js/parsephphtmlmixed.js"],    
          stylesheet: ["codemirror/css/xmlcolors.css", "codemirror/css/csscolors.css", "codemirror/contrib/php/css/phpcolors.css", "codemirror/css/jscolors.css"],    
          path: "codemirror/js/",    
          autoMatchParens: true,
          lineNumbers : true,
          indentUnit : 2,
          tabMode : "classic",
          syntax: 'html',
          onLoad: function(editor) {
            zen_editor.bind(editor);
          }
        });
        
        
        // hide the docMenu when the code iframe is clicked
        z.codeIframe = $($("iframe").first()[0].contentWindow);
        z.codeIframe .css("height", "99%");
        z.codeIframe.click(function(){
          z.docMenu.hide();
          z.hideColorPicker();
        });
        
        // update some elements on resize
        z.win.resize(function(){
          z.modal.css({
            top : z.win.height() / 2 - z.modal.outerWidth() / 2,
            left : z.win.width()  / 2 - z.modal.outerHeight() / 2
          });
          z.overlay.css({
            width : z.win.width(),
            height : z.win.height()
          });
          
          z.files.css({height : z.win.height() - z.files.position().top-25});
          
          $(".CodeMirror-wrapping").css("height", z.win.height() - z.codeUI.outerHeight());
          
        }).trigger("resize");
        
        
        
        // mouseX and Y
        z.doc.mousemove(function(e){
          z.mouseX = e.pageX;
          z.mouseY = e.pageY;
        });
        

        z.keyup = function(e){
          if (z.autoTestCheck.attr("checked")){
            clearTimeout(z.autoTest);
            z.autoTest = setTimeout(z.saveFileFolder,1000);
          }
        };
        
        $("iframe").each(function(i, item){
          $(item.contentWindow).keyup(z.keyup);
        });
        
        
        z.updatePreview = function(){
           
          if (z.currentPath.match(z.visualTypes)){
            z.visualPreview = z.currentPath;
          }
          if (z.autoTestCheck.attr("checked")){
            z.previewFrame.attr("src", z.visualPreview);
          }else{
            z.previewFrame.attr("src", "splash.html");
          }
        };
        
        // called when a new file or folder is created
        z.saveNewFileFolder = function(path){
          
          var data = {name:path, content:""};
          
          $.post("save.php", data, function(data){
            if (z.newFileType == "file"){
              z.currentPath = path;
              z.saveOnClick = false;
              z.newNode.trigger("click");
              z.saveOnClick = true;
              z.editor.setCode("");
              
            }
          });
          
        };
        
        // saves a file or folder
        z.saveFileFolder = function(callback){
           
          if (!callback) callback = function(){};
          if (!z.currentPath) return;
            
            var data = {name:z.currentPath, content:encodeURIComponent(z.editor.getCode()).replace(/\'/g, "%27")};
          
          $.post("save.php", data, function(data){
            z.updatePreview();
            callback();
          });
        };
        
      
        
        // create instances of main classes
        z.dragCols = new z.DragCols();
        z.fileList = new z.FileList();
        z.codeWin = new z.CodeWin();
        
       
        
        
      });
    </script>
    <style>
      body,html{
        margin : 0px;
        padding : 0px;
        font-family : sans-serif;
        font-size : 12px; 
        overflow : hidden;
      }
      li{
        cursor : pointer; 
      }
    
      #files,#codeCol,#preview{
        position : absolute;
        outline : 1px solid black; 
        height : 100%;
      }
      #files{
        left : 0px;
        top : 0px;
        width : 200px;
        overflow : hidden;
      }
      #codeCol{
        left : 200px;
        width : 700px; 
        height : 100%;
        overflow : hidden;
      }
      #codeUI{
        position : relative;
        height : 65px; 
        min-width : 570px;
        overflow : hidden;
      }
      #codeUI a{
        display : block;
        float : left;
        padding-right : 10px;
        margin-right : 10px;
        border-right : 1px solid black; 
      }
      #fontSize{
        float : left;
        margin-top : -2px; 
      }
      .CodeMirror-line-numbers {
        width: 2.2em;
        color: #AAA;
        background-color: #EEE;
        text-align: right;
        padding-right: .3em;
        font-size: 10pt;
        font-family: monospace;
        padding-top: .4em;
      }
      .content{
        padding : 10px; 
      }
      #topFolder{
        width : 100%; 
      }
      a,a:visited{
        color : black;
        text-decoration : none; 
      }
      a:hover{
        color:#45729e;
      }
      #refresh{
        display : block;
        padding  : 5px; 
      }
      #fileList{
        margin-top : 15px; 
        border-top : 1px solid gray;
        padding-top : 10px;
        height : 500px;
        overflow : auto;
      }
      #previewFrame{
        width  : 100%;
        height : 100%;
        border : none; 
      }
      #docMenu{
        position : absolute;
        display : none; 
      }
      #docMenu ul{
        font-family : sans-serif;
        font-size : 12px;
        padding :0;
        margin : 0;
        border : 1px solid black;
        background-color : #aaa;
        width : 110px;
        border-bottom : none;
        
      }
      #docMenu li{
        padding : 4px;
        border-bottom: 1px solid black;
        background-color : white;
        list-style : none;
        cursor : pointer;
        
        background-image : none !important;
      }
      #docMenu li:hover{
        background-color : #ccc; 
      }
      .left{
        position :relative;
        float : left; 
      }
      .right{
        position : relative;
        float : right; 
      }
      .noSelect{
        -moz-user-select: -moz-none;
        -khtml-user-select: none;
        -webkit-user-select: none;
        user-select: none;
      }
      button{
        float : left;
        padding : 0;
        margin : 0;
        margin-top: 1px;
        padding : 5px 10px 5px 10px;
        border : none;
        border-left : 1px solid #aaa;
        cursor : pointer;
        background-color : white;
      }
      button:hover{
        color :  #078be3;
      }
      #renameBtn{
        float : right;
        margin-right : 10px;
        margin-left : 10px;
      }
      .cancel{
        float : right;
      }
      #renameBtns{
        padding-top : 23px;
      }
      .saveCancel{
        margin-top : -10px;
      }
      #ok{
        margin-top : 20px;
      }
      #modal button{
        border-radius : 4px;
        border : 2px solid white;
        background-color : #333333;
        color : #dddddd;
      }
      
      #modal button:hover{
        background-color : black;
        color : white;
      }
      .defaultBtn{
        border-color : #078be3 !important;
      }
      #find{
        margin-top : -10px;
      }
      
      #find textarea{
        resize : none;
      }
      
      #find button{
        width : 90px;
        padding : 4px;
        margin-bottom : 10px;
      }
      #cover{
        background-color : black;
        position : absolute;
        display : none;
      }
      
      #findReplaceBtns{
        margin-top : -114px;
        margin-right : 20px;
      }
      #fileName{
        position : relative;
        clear : both;
        background-color : #ccc; 
        padding : 10px;
      }
      #uiContent{
        height : 13px;
      }
      
      #color{
        width : 20px;
        height : 20px;
        margin-left : 10px;
        margin-top : -6px;
        float :left;
      } 
      #color div{
        float : left;
        width : 20px;
        height : 20px;
        border-radius : 4px;
        margin-top : 3px;
        margin-left : -2px;
      } 
     
      
    </style>
    
  </head>
  <body>
    <div id="files">
      <div class="content">
        <a id="refresh" href="#">Refresh List</a>
        <select id="topFolder">loading...</select>
        <div id="fileList" class="noSelect">
          
        </div>
      </div>
    </div>
    <div id="codeCol">
      <div id="codeUI" class="noSelect">
        <div class="content" id="uiContent">
          <div class="left">
            <a id="newBtn" href="#">New</a>
            <a id="autoFormat" href="#">Auto Format</a>
            <a id="findReplaceBtn" href="#">Find and Replace</a>
            <select id="fontSize">
              <option>10px</option>
              <option>12px</option>
              <option selected>13px</option>
              <option>14px</option>
              <option>18px</option>
              <option>20px</option>
              <option>24px</option>
              <option>28px</option>
            </select>
            
            <div id="color" style="display:inline"><div style="background-color: #000000"></div></div>
          </div>
          <div class="right">
            
            <a href="#" id="test">Test</a>
            <a href="#" id="testInTab">Test in Tab</a>
            <input id="autoTest" type="checkbox" checked/>&nbsp;Auto Test
          </div>
        </div>
        <div id="fileName">
          --
        </div>
        
      </div>
      <div id="codeBorder">
        <textarea id="code"></textarea>
      </div>
    </div>
    <div id="preview"><iframe id="previewFrame" src="splash.html" ></iframe></div>
    
    <div id="docMenu" class="noSelect">
      <ul>
        <li>New Document</li>
        <li>Rename</li>
        <!--<li>Duplicate</li>-->
        <li>Delete</li>
      </ul>
    </div>
    
    
    <div id="modal">
      <div id="modalTitle">New File or Folder</div>
      <div id="modalContent">
        <div id="find" class="modalUI">
          <div class="left findReplace">
            <form id="findIt">
              Find: <br/><textarea name="search" id="search" ></textarea><br/><br/>
              Replace: <br/><textarea  name="replace" id="replace"></textarea><br/>
            </form>
          </div>
          <div class="right" id="findReplaceBtns">
            <br/>
            <button id="findBtn" >Find</button><br/><br/>
            <button id="replaceBtn">Replace All</button><br/><br/>
            <button id="okBtn">ok</button>
          </div>
        </div>
        
        <div id="rename" class="modalUI">
          <b>Rename:</b>
          <span>...</span><br/><br/>
          <form id="renameIt">
            New Name: <input type = "text" name="renameName" id="renameName" />
            
            
          </form>
          <div id="renameBtns">
            <button id="renameBtn" class="defaultBtn">Rename</button>
            <button class="cancel">Cancel</button>
          </div>
          
          
        </div>
        
        <div id="newDialogue" class="modalUI">
          
          <b>Creating Files and Folders</b><br/><br/>
          Right + click or control + click a file or folder and select "New Document" from the dropdown.
          <button id="ok" class="defaultBtn">ok</button>
        </div>
        
        <div id="newDocument" class="modalUI">
          Path : <span>path</span>
          <div class="smallbr"><br/><br/></div>
          <form id="create">
            <div class="left">
              File/Folder Name <br/>
              <input type="text" name="filename" id="filename"/>
            </div>
            
          </form>
          <div class="saveCancel">
            <button class="cancel">Cancel</button>
            <button id="save" class="defaultBtn">Save</button>
          </div>
        </div>
      </div>
      
    </div>
    
  </body>
</html>