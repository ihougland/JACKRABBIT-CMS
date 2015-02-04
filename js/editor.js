$(document).ready(function() {

    function pasteHtmlAtCaret(html) {
        var sel, range;
        if (window.getSelection) {
            // IE9 and non-IE
            sel = window.getSelection();
            if (sel.getRangeAt && sel.rangeCount) {
                range = sel.getRangeAt(0);
                range.deleteContents();

                // Range.createContextualFragment() would be useful here but is
                // non-standard and not supported in all browsers (IE9, for one)
                var el = document.createElement("div");
                el.innerHTML = html;
                var frag = document.createDocumentFragment(),
                    node, lastNode;
                while ((node = el.firstChild)) {
                    lastNode = frag.appendChild(node);
                }
                range.insertNode(frag);

                // Preserve the selection
                if (lastNode) {
                    range = range.cloneRange();
                    range.setStartAfter(lastNode);
                    range.collapse(true);
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            }
        } else if (document.selection && document.selection.type != "Control") {
            // IE < 9
            document.selection.createRange().pasteHTML(html);
        }
    }

    /**** Editor Nav Dropdowns ****/
    $('.editor-drop').hover(
        function() {
            $(this).find('ul').slideDown(100);
        },
        function() {
            $(this).find('ul').slideUp(100);
        }
    );
    /**** Disable firefox helpers ****/

    /* currently applying to everything, not just editor
    document.designMode = "on";
    document.execCommand('enableObjectResizing', false, 'false');
    document.execCommand('enableInlineTableEditing', false, 'false');
    */

    /**** Fix Copy & Paste formatting ****/
    $('[contenteditable]').on('paste', function(e) {
        e.preventDefault();
        var text = (e.originalEvent || e).clipboardData.getData('text/plain') || prompt('Paste something..');
        document.execCommand('insertText', false, text);
    });
    /**** Function to keep right-click menues on the screen ****/
    function menuPosition() {
            var menuHeight = $('.custom-menu').height(),
                menuWidth = $('.custom-menu').width(),
                menuLeft = $('.custom-menu').offset().left,
                menuTop = $('.custom-menu').offset().top,
                windowWidth = $(window).width(),
                windowHeight = $(window).height();
            /* if out of bounds on left AND bottom */
            if (((menuLeft + menuWidth) > windowWidth) && ((menuTop + menuHeight) > windowHeight)) {
                $('.custom-menu').css({
                    "left": (menuLeft - menuWidth),
                    "top": (menuTop - menuHeight)
                });
                /* if out of bounds on left only */
            } else if ((menuLeft + menuWidth) > windowWidth) {
                $('.custom-menu').css("left", (menuLeft - menuWidth));
                /* if out of bounds on bottom only */
            } else if ((menuTop + menuHeight) > windowHeight) {
                $('.custom-menu').css("top", (menuTop - menuHeight));
            }


        }
        /*
        d888888b .88b  d88.  .d8b.   d888b  d88888b .d8888. 
          `88'   88'YbdP`88 d8' `8b 88' Y8b 88'     88'  YP 
           88    88  88  88 88ooo88 88      88ooooo `8bo.   
           88    88  88  88 88~~~88 88  ooo 88~~~~~   `Y8b. 
          .88.   88  88  88 88   88 88. ~8~ 88.     db   8D 
        Y888888P YP  YP  YP YP   YP  Y888P  Y88888P `8888Y' 
        */

    /* Prevent dragging */
    $('div').bind('dragover drop', function(event) {
        event.preventDefault();
        return false;
    });
    // Add Image
    //Upload Picture
    /*
        $('.uploadpic').click(function (){
            image = $('#image1').val();
            alt = $('#alt_text').val();
            alert(image+" "+alt);
            
            $.post(  
                "uploader.php", //The update file
                {'image': image, 'alt': alt},  // create an object will all values
                //function that is called when server returns a value.
                function(data){
                    alert(data.file_name);
                }, 
                //How you want the data formated when it is returned from the server.
                "json"
            );
        });

        function addImage(){
            //open modal
            $('.modal').fadeIn();
        }
*/

    // Make the menu
    $(document).on('click', '.editor-text img', function(event) {
        if ($(this).hasClass("selectedImg")) {
            doneWithImage();
        } else {
            event.preventDefault();
            $(this).addClass('selectedImg');
            $('.editor-text').attr('contenteditable', false).css('color', '#ccc');
            $(".editor-toolbar a").css({
                'pointer-events': 'none',
                'opacity': '.1'
            });
            $('<div id="image-toolbar"><h1>Image:</h1> <a href="#" class="fl">Wrap Left</a><a href="#" class="fr">Wrap Right</a><a href="#" class="fc">Centered</a><a href="#" class="fn">Inline</a><a href="#" class="fd">Delete</a></div>')
                .appendTo(".menu-bar")
                .css({
                    top: event.pageY + "px",
                    left: event.pageX + "px"
                }).fadeIn(300, "easeOutExpo");
        }
    });

    function doneWithImage() {
        $("#image-toolbar").remove();
        $('.selectedImg').removeClass('selectedImg');
        $('.editor-text').attr('contenteditable', true).css('color', '');
        $(".editor-toolbar a").css({
            'pointer-events': 'auto',
            'opacity': '1'
        });
    }


    // Apply right
    $(document.body).on("click", ".fr", function(event) {
        $('.selectedImg').removeClass().addClass('float-right').removeAttr('id');
        doneWithImage();
        event.preventDefault();
    });

    // Apply left
    $(document.body).on("click", ".fl", function(event) {
        $('.selectedImg').removeClass().addClass('float-left').removeAttr('id');
        doneWithImage();
        event.preventDefault();
    });

    // Apply normal
    $(document.body).on("click", ".fn", function(event) {
        $('.selectedImg').removeClass().addClass('float-normal').removeAttr('id');
        doneWithImage();
        event.preventDefault();
    });

    // Apply center
    $(document.body).on("click", ".fc", function(event) {
        $('.selectedImg').removeClass().addClass('float-center').removeAttr('id');
        doneWithImage();
        event.preventDefault();
    });

    // Apply delete
    $(document.body).on("click", ".fd", function(event) {
        $('.selectedImg').fadeOut(500, function() {
            $(this).remove();
        });
        doneWithImage();
        event.preventDefault();
    });

    /*
    d888888b  .d8b.  d8888b. db      d88888b .d8888. 
    `~~88~~' d8' `8b 88  `8D 88      88'     88'  YP 
       88    88ooo88 88oooY' 88      88ooooo `8bo.   
       88    88~~~88 88~~~b. 88      88~~~~~   `Y8b. 
       88    88   88 88   8D 88booo. 88.     db   8D 
       YP    YP   YP Y8888P' Y88888P Y88888P `8888Y'
    */
    // Hover Table Dropdown
    $(document).on("mouseenter", ".cell-add", function(event) {
        // How many rows/cols have you selected?
        var hoveredCols = $(this).index() + 1,
            hoveredRows = $(this).parent().index() + 1,
            //How many total?
            availRows = $('.row-add').length,
            availCols = $('.row-add:first .cell-add').length;

        $('.cell-highlight').removeClass('cell-highlight');
        // Update the text hint
        $('.table-dimension-result').html(hoveredRows + ' x ' + hoveredCols);
        // Add Highlighting to selected squares from top left to cursor
        $('.row-add:lt(' + hoveredRows + ') .cell-add:not(:nth-child(1n+' + (hoveredCols + 1) + '))').addClass('cell-highlight');
        // add rows as needed
        if (hoveredRows == availRows) {
            $('.row-add:first').clone().appendTo('.table-dimension');
            $('.row-add:last .cell-add').removeClass('cell-highlight');
        }
        if (hoveredCols == availCols) {
            $('.row-add').append('<a href="#" class="cell-add"></a>');
        }
    });
    // Mouse Out
    $(document).on("mouseleave", ".table-dimension", function(event) {
        // Remove old highlighting
        $('.cell-highlight').removeClass('cell-highlight');
        $('.table-dimension-result').html('0 x 0');
        var table = '';
        for (var i = 1; i <= 4; i++) {
            table += '<div class="row-add">';
            for (var j = 1; j <= 4; j++) {
                table += '<a href="#" class="cell-add"></a>';
            }
            table += '</div>';
        }
        $('.table-dimension').html(table);
    });
    // Insert Table
    $(document.body).on("click", ".cell-add", function(event) {
        event.preventDefault();
        // get # of rows & cols
        var cols = $(this).index() + 1,
            rows = $(this).parent().index() + 1;
        if (cols != 0 && rows != 0) {
            var table = '<table class="rwd-table" width="100%">';
            for (var i = 1; i <= rows; i++) {
                table += '<tr>';
                for (var j = 1; j <= cols; j++) {
                    table += '<td>&nbsp;</td>';
                }
                table += '</tr>';
            }
            table += '</table>';

            pasteHtmlAtCaret(table);
        }
    });
    // Right Click
    $(function() {
        $(document).on('contextmenu', 'td, th', function(event) {
            $(this).attr('id', 'selected');
        });
    });
    // Make the menu
    $(document).on('contextmenu', 'td, th', function(event) {
        event.preventDefault();
        $('.custom-menu').remove();
        $('<div class="custom-menu"><a href="#" class="addhead"><i class="fa fa-header"></i> Add Table Header</a><a href="#" class="addrow"><i class="fa fa-plus"></i> Add Row After</a><a href="#" class="delrow"><i class="fa fa-trash-o"></i> Delete Row</a><a href="#" class="addcol"><i class="fa fa-plus"></i> Add Column After</a><a href="#" class="delcol"><i class="fa fa-trash-o"></i> Delete Column</a><a href="#" class="deltable"><i class="fa fa-trash-o"></i> Delete Entire Table</a></div>')
            .appendTo("body")
            .css({
                top: event.pageY + "px",
                left: event.pageX + "px"
            }).fadeIn();
        /* Fix menu position if needed */
        menuPosition();
    });


    // Add Header
    $(document.body).on("click", ".addhead", function(event) {
        $('#selected').parents('table').attr('id', 'selectedTable');
        var cols = $('#selectedTable tr:first-of-type td').length;

        $('#selectedTable').prepend('<thead><tr id="selectedRow"></tr></thead>')
        for (var i = 0; i < cols; i++) {
            $('#selectedRow').append('<th>&nbsp;</th>');
        }

        $('#selectedRow, #selected, #selectedTable').removeAttr('id');
        event.preventDefault();
    });

    // Add Row
    $(document.body).on("click", ".addrow", function(event) {
        $('#selected').parents('table').attr('id', 'selectedTable');
        var whichOne = $('#selected').parent().index(),
            cols = $('#selectedTable tr:first-of-type td').length;

        $('<tr id="selectedRow"></tr>').insertAfter("#selectedTable tr:eq(" + whichOne + ")");
        for (var i = 0; i < cols; i++) {
            $('#selectedRow').append('<td>&nbsp;</td>');
        }
        $('#selectedRow, #selected, #selectedTable').removeAttr('id');
        event.preventDefault();
    });

    // Delete Row
    $(document.body).on("click", ".delrow", function(event) {
        $('#selected').parents('tr').fadeOut(500, function() {
            $(this).remove();
        });
        event.preventDefault();
    });

    // Add Column
    $(document.body).on("click", ".addcol", function(event) {
        $('#selected').parents('table').attr('id', 'selectedTable');
        var whichOne = $('#selected').index(),
            rows = $('#selectedTable tr').length;

        $('#selectedTable tr').each(function() {
            $('<td>&nbsp;</td>').insertAfter($(this).find('td:eq(' + whichOne + ')'));
        });

        $('#selected, #selectedTable').removeAttr('id');
        event.preventDefault();
    });

    // Delete Col
    $(document.body).on("click", ".delcol", function(event) {
        $('#selected').parents('table').attr('id', 'selectedTable');
        var whichOne = $('#selected').index();
        $('#selectedTable tr').find("td:eq(" + whichOne + ")").fadeOut(500, function() {
            $(this).remove();
        });
        $('#selected, #selectedTable').removeAttr('id');
        event.preventDefault();
    });

    // Delete Entire Table
    $(document.body).on("click", ".deltable", function(event) {
        var r = confirm("Are you sure you want to delete the whole table");
        if (r == true) {
            $('#selected').parents('table').fadeOut(500, function() {
                $(this).remove();
            });
        }
        event.preventDefault();
    });


    /*** HTML MODE ***/
    $(document.body).on("click", "#htmlMode", function(event) {

        // If already in html mode
        if ($(this).hasClass('on')) {
            $(this).removeClass('on');
            var test = editor.getValue();
            alert(test);

        } else {
            $(this).addClass('on');
            var saveText = $('.editor-text').html();
            $('.editor-text').css({
                'transform': 'scale(.8)',
                'opacity': '0'
            });

            setTimeout(function() {
                $('.editor-text').hide();
                $('.html-mode textarea').html(saveText);

                var myCodeMirror = CodeMirror(function(elt) {
                    htmlTextarea.parentNode.replaceChild(elt, htmlTextarea);
                }, {
                    value: htmlTextarea.value,
                    lineNumbers: true,
                    lineWrapping: true,
                    viewportMargin: Infinity
                });
                $('.html-mode').fadeIn();
                $('.CodeMirror').each(function(i, el) {
                    el.CodeMirror.refresh();
                });
            }, 250);

        }



    });

    /**** Insert BR on enter ****/
    $('div[contenteditable]').keydown(function(e) {
        if (e.keyCode === 13) {
            document.execCommand('insertHTML', false, '<br>');
            return false;
        }
    });
    /**** Remove Header Formatting ****/
    function nextNode(node) {
        if (node.hasChildNodes()) {
            return node.firstChild;
        } else {
            while (node && !node.nextSibling) {
                node = node.parentNode;
            }
            if (!node) {
                return null;
            }
            return node.nextSibling;
        }
    }

    function getRangeSelectedNodes(range, includePartiallySelectedContainers) {
        var node = range.startContainer;
        var endNode = range.endContainer;
        var rangeNodes = [];

        // Special case for a range that is contained within a single node
        if (node == endNode) {
            rangeNodes = [node];
        } else {
            // Iterate nodes until we hit the end container
            while (node && node != endNode) {
                rangeNodes.push(node = nextNode(node));
            }

            // Add partially selected nodes at the start of the range
            node = range.startContainer;
            while (node && node != range.commonAncestorContainer) {
                rangeNodes.unshift(node);
                node = node.parentNode;
            }
        }

        // Add ancestors of the range container, if required
        if (includePartiallySelectedContainers) {
            node = range.commonAncestorContainer;
            while (node) {
                rangeNodes.push(node);
                node = node.parentNode;
            }
        }

        return rangeNodes;
    }

    function getSelectedNodes() {
        var nodes = [];
        if (window.getSelection) {
            var sel = window.getSelection();
            for (var i = 0, len = sel.rangeCount; i < len; ++i) {
                nodes.push.apply(nodes, getRangeSelectedNodes(sel.getRangeAt(i), true));
            }
        }
        return nodes;
    }

    function replaceWithOwnChildren(el) {
        var parent = el.parentNode;
        while (el.hasChildNodes()) {
            parent.insertBefore(el.firstChild, el);
        }
        parent.removeChild(el);
    }

    function removeSelectedElements(tagNames) {
        var tagNamesArray = tagNames.toLowerCase().split(",");
        getSelectedNodes().forEach(function(node) {
            if (node.nodeType == 1 &&
                tagNamesArray.indexOf(node.tagName.toLowerCase()) > -1) {
                // Remove the node and replace it with its children
                replaceWithOwnChildren(node);
            }
        });
    }

    document.getElementById("removeHeadings").onclick = function() {
        removeSelectedElements("h1,h2,h3,h4,h5,h6");
        document.execCommand('removeFormat', false, 'null');
        return false;
    };
});
