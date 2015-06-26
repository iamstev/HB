<?php
    print'
        <footer class="footer">
            <div class="footer-logo">
                <img src="/img/helmar_logo_white.png" alt="Helmar Brewing Co Logo">
            </div>
            <div class="footer-links">
                <ul>
                    <li><h3>Content</h3></li>
                    <li><a href="'.$protocol.$site.'/artwork/">Helmar Card Art</a></li>
                    <li><a href="'.$protocol.$site.'/about/">Helmar &amp; Charles</a></li>
                <!--    <li><a href="">Articles</a></li>                -->
                    <li><a href="'.$protocol.$site.'/subscription">Digital Magazine</a></li>
                    <li><a href="http://helmarblog.com/" target="_blank">Blog</a></li>
                </ul>
                <ul>
                    <li><h3>Connect</h3></li>
                    <li><a href="">Facebook</a></li>
                    <li><a href="http://stores.ebay.com/Helmar-Brewing-Art-and-History/" target="_blank">eBay</a></li>
                    <li><a href="'.$protocol.$site.'/contact/">Contact</a></li>
                </ul>
                <ul>
                    <li><h3>Account</h3></li>
    ';
    if(isset($user)){
        if( $user->login() == 1 || $user->login() == 2 ){
            print'
                        <li><a href="'.$protocol.$site.'/account/logout">Log out</a></li>
            ';
        }else{
            print'
                        <li><a href="'.$protocol.$site.'/account/login?redir='.$currentpage.'">Login</a></li>
                        <li><a href="'.$protocol.$site.'/account/register">Register</a></li>
            ';
        }
    }else{
        print'
                        <li><a href="'.$protocol.$site.'/account/login?redir='.$currentpage.'">Login</a></li>
                        <li><a href="'.$protocol.$site.'/account/register">Register</a></li>
        ';
    }
    print'
                        <li><a href="'.$protocol.$site.'/account/">Manage Account</a>
                        <li><a href="'.$protocol.$site.'/checklist/">Personal Checklist</a></li>
                        <li><a href="'.$protocol.$site.'/subscription">Subscription</a></li>
                <!--    <li><a href="">Terms and Conditions</a></li>    -->
                <!--    <li><a href="">Privacy Policy</a></li>          -->
                </ul>
            </div>
            <hr>
            <p>All content &copy; '.date("Y").' Helmar Brewing Co.</p>
        </footer>
        <div class="modal-holder" id="ajax-modal">
            <div class="modal-wrap">
                <div class="modal">
                    <i id="modal_close" class="close fa fa-times" onclick="hideModal(\'ajax-modal\');"></i>
                    <h1 id="modal_h1"></h1>
                    <div id="modal_content"></div>
                </div>
            </div>
        </div>
        <div id="blackout" class="blackout"></div>
        <div id="fullscreenload" class="fullscreenload"><span></span><img src="/img/loading.gif"></div>
    ';
    //    /* SESSION DEBUGGING */ print'<pre style="font-family:monospace;background-color:#444;padding:1em;color:white;">';var_dump($_SESSION);print'</pre>';
    print'
    </body>
    </html>
    ';
?>
