<?php
        
    /**
    *   prints the head of the page and the first part of the body that is in common between al pages.
    *   the head can be modified through arguments
    *   Params: 
    *       @param string $pageTitle - the title of the page
    *       @param array $styles - style sheets to load in the page
    *       @param array $scripts - script files to load in the page
    *       @param bool/int $login - tells what nav bar display true(navbar when logged in) fasle(navbar when not logged in) -1(no navbar)
    *       @return void
    */
    function printHead($pageTitle, $styles = [], $scripts = [], $login = true)
    {
        $styleTags = '';
        $scriptTags = '';

        foreach($styles as $style)
            $styleTags = $styleTags . '<link rel="stylesheet" href="' . trim($style) . '" />';

        foreach($scripts as $script)
            $scriptTags = $scriptTags . '<script src="' . trim($script) . '" defer /></script>';

      echo "<!DOCTYPE html>
            <html lang=\"it\">
            <head>
                <meta charset=\"UTF-8\">
                <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
                <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
                <link rel=\"icon\" href=\"".$GLOBALS['logo']."\">
                <title>$pageTitle</title>
                $styleTags
                $scriptTags
                <style>
                    body {
                        background: url(".$GLOBALS['background'].") no-repeat center fixed;
                        background-size: cover;
                    }
                    :root {
                        --clr--background-shade: ".$GLOBALS['backgroundFilterColor']."B3;
                    }
                </style>

            </head>
            
            <body>";

        if($login !== -1)
            require_once ABSPATH."/UIComponents/" . ( $login ? "nav.php" : "nav_nologin.php" );       

        echo " <div class=\"body-cover\"></div> ";
    }


    /**
    *   prints the footer of the page and the closing tag 
    *   Params: 
    *       @return void
    */
    function printFooter()
    {
        require_once ABSPATH."/UIComponents/footer.php";

        echo "</body>
              </html>"; 
    }

    /**
     *  prints the input text component
     *  Params: 
     *      @param String $label label of the input text component
     *      @param String $name name prop of the input tag inside the component
     *      @param String $type type prop of the input tag inside the component
     *      @param Array $classes array of addition css classes 
     *      @param String $initValue initial value
     *      @return void
     */
    function inputText($label, $name, $type = 'text', $classes = [], $initValue = null) {
        echo "<div class='input-text  ".(implode(' ', $classes))."'>
            <input type='$type' name='$name' class='input-text__text' ".($initValue != null ? "value='$initValue'" : "")." autocomplete='off' required> 
            <label for='$name'>$label</label>
        </div>";
    }

    /**
     *  prints the input radio component
     *  Params: 
     *      @param String $name name prop of the input tag inside the component
     *      @param String $value value prop of the input tag inside the component
     *      @param String $label label of the input radio component
     *      @param bool $checked initial value of the checkbox
     *      @return void
     */
    function inputRadio($name, $value, $label, $checked = false) {
        echo "<div>
            <input type='radio' name='$name' value='$value' id='$value' ".($checked ? 'checked' : '')." required>
            <label for='$value'>$label</label>
        </div>";
    }

    /**
     *  prints the text area component
     *  Params: 
     *      @param String $label label of the input radio component
     *      @param String $name name prop of the input tag inside the component
     *      @param Array $classes array of addition css classes 
     *      @param String $initValue initial value
     *      @param int $cols number of columns in the text area
     *      @param int $rows number of rows in the text area
     *      @return void
     */
    function textArea($label, $name, $classes = [], $initValue = '', $cols = 30, $rows = 10) {
        echo "<div class='input-text ".(implode(' ', $classes))."'>
                <label for='$name' class='input-text__area-label'>$label</label>
                <textarea cols='$cols' rows=' $rows' name='$name' class='input-text__area' required>$initValue</textarea>
              </div>";
    }

    /**
     * prints the back arrow icon
     */
    function backArrow() {
        echo '<span class="back-arrow"><svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                   viewBox="0 0 330 330" style="enable-background:new 0 0 330 330;" xml:space="preserve">
                <path id="XMLID_225_" d="M325.607,79.393c-5.857-5.857-15.355-5.858-21.213,0.001l-139.39,139.393L25.607,79.393
                c-5.857-5.857-15.355-5.858-21.213,0.001c-5.858,5.858-5.858,15.355,0,21.213l150.004,150c2.813,2.813,6.628,4.393,10.606,4.393
                s7.794-1.581,10.606-4.394l149.996-150C331.465,94.749,331.465,85.251,325.607,79.393z"/>
              </svg> Indietro</span>';
    }