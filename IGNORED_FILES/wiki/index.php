<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title>Wiki Web Help</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        
        <link rel="stylesheet" type="text/css" href="theme/default/css/ui.css" />
        <link rel="stylesheet" type="text/css" href="theme/default/css/tabs.css" />
        <link rel="stylesheet" type="text/css" href="theme/default/css/tree.css" />
        <link rel="stylesheet" type="text/css" href="theme/default/css/popupform.css" />
        <script type="text/javascript" language="javascript" src="script/ui.js"></script>
        <script type="text/javascript" language="javascript" src="script/tabs.js"></script>
        <script type="text/javascript" language="javascript" src="script/tree.js"></script>
        <script type="text/javascript" language="javascript" src="script/popupform.js"></script>
        <script type="text/javascript" language="javascript" src="script/wicky/wiky.js"></script>
        <link rel="icon" type="image/ico" href="favicon.ico" />
    </head>
    <body onload="loading();">
        <div id="header">
            <a id='LocationAnchor' style="position:absolute;top:0;"></a>
            <a href="javascript:tree.click(tree.home)">
                <div id="logo">
                    <img src="theme/default/images/wh32.png" alt="logo"/>
                </div>
                <div id="sitetext">Wiki Web Help</div>
            </a>
            <div class='nav'>
                <input type="button" value=" &lt; " onclick="back();"/><input type="button" value=" &gt; " onclick="forward();"/>
            </div>
            <div style="margin-right:22px;">
                <div id="logoutmenu" class="menu" style="display:none;">
                    <a id="logouta" href="javascript:logout()">Logout</a>
                </div>
                <div id="profilemenu" class="menu" style="display:none;">
                    <div style="position:relative;">
                        <div style="position:absolute;left:-222px;width:260px;" id='profile'>
                        </div>
                        <a id="profilea" href="javascript:profile()">Profile</a>
                    </div>
                </div>
                <div id="loginmenu" class="menu">
                    <div style="position:relative;">
                        <div style="position:absolute;left:-222px;width:260px;" id='login'>
                        </div>
                        <a id="logina" href="javascript:loginform();">Login</a>
                    </div>
                </div>
                <div id='revform' style='display:none;position:absolute;top:50px;right:250px'></div>
                <div id="registermenu" class="menu">
                    <div style="position:relative;">
                        <div style="position:absolute;left:-190px;width:260px;" id='register'>
                        </div>
                        <a id="registera" href="javascript:registerform();">Register</a>
                    </div>
                </div>
                <div class="menu">
                    <a id="historyna" href="javascript:getnodehistory()">Tree History</a>
                </div>
                <div class="menu">
                    <div id="lang">
                        <select id="langsel" onchange="changelanguage();">
                            <option value=''>&nbsp;</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="clear">
        </div>
        <div style="position:relative;height:100%;">
            <div id="control" class="control">
                <div id="pane_a">
                    <div id="tabdiv" class="tabpane">
                        <div id="ctab1" class="tabcontent">
                            <div class="tabpad">
                                <div id="toc">
                                    <div id="tree">
                                    </div>
                                    <div class="clear">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="ctab2" class="tabcontent">
                            <div class="tabpad">
                                <div class="indexform">
                                    <form action="" onsubmit="return false;">
                                        <div id="indextype">
                                            Type in the keyword to find
                                        </div>
                                        <div>
                                            <input id="index" onkeyup="indexkey();" style="width:97%;"/>
                                        </div>
                                        <div id="indexpane">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div id="ctab3" class="tabcontent" style="overflow:hidden;">
                            <div class="tabpad">
                                <div class="indexform">
                                    <div style="width:500px;overflow:hidden">
                                        <div id="searchlabel">
                                            Search:
                                        </div>
                                        <div id="searchbox">
                                            <input id="keyword" onclick="this.select();" onkeypress="searchkey(event);"/>
                                        </div>
                                    </div>
                                    <div id="searchresult">
                                        <div class="clear">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="splitter" title="Double click to collapse / expand" class="split" onmousedown="md();" onmouseup="mu();" ondblclick='dc()'>
                    &nbsp;
                    <div class="clear">
                    </div>
                </div>
                <div id="treemenu">
                </div>
                <div id="pane_b">
                    <div id="crumbs" class="commande_message"></div>
                    <div class="menu" id="tagsm">
                        <div style="position:relative;">
                            <div style="position:absolute;left:-360px;width:260px;" id='tags'>
                            </div>
                            <a id="tagsa" href="javascript:tags();">Edit Tags</a>
                        </div>
                    </div>
                    <div id="taglist">
                    </div>
                    <div id="pagemenu">
                        <a class="menupage" id="viewa" href="javascript:tree.click(tree.activenode);">View</a>
                        <a class="menupage" id="edita" href="javascript:edit()">Edit</a>
                        <a class="menupage" id="historya" href="javascript:gethistory()">History</a>
                        <a class="menupage" id="adminmenu" href="javascript:adminpage()" style="display:none;">Admin</a>
                        <div class="menu">
                            <div id="printa"><a href="javascript:printpage()"><img id="printi" alt="print" src="images/system/print.png" /></a></div>
                        </div>
                        <div class="clear">
                        </div>
                    </div>
                    <div id="help">
                        <div class="clear">
                        </div>
                    </div>
                </div>
                <div class="clear">
                </div>
            </div>
            <div class="clear">
            </div>
        </div>
        <div id="highlight">
        </div>
        <input type='hidden' id='previewtext' value='' />
        <div style='display:none;'>
            <?php $configpath = ".";
            include ('pages/links.php'); ?>
        </div>

    </body>
</html>
