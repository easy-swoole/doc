<!DOCTYPE html>
<html lang="cn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="/Css/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <link href="/Css/contentPage.css" rel="stylesheet">
    <link rel="stylesheet" href="/Css/document.css">
    <link rel="stylesheet" href="/Css/highlight.css">
    <link rel="stylesheet" href="/Css/markdown.css">
    <script src="/Js/jquery.min.js"></script>
    <script src="/Js/highlight.min.js"></script>
    <script src="/Js/js.cookie.min.js"></script>
    <script src="/Js/global.js"></script>
    <script src="/Js/jquery.mark.min.js"></script>
    <script src="/Js/Layer/layer.js"></script>
    {if isset($page.config.title)}<title>{$page.config.title}</title>{/if}

    {if isset($page.config.meta)}
        {foreach from=$page.config.meta item=item key=key}
            <meta name="{$item.name}" content="{$item.content}">
        {/foreach}
    {/if}

</head>
<body>
<div class="container layout-1">
    <a class="sideBar-toggle-button" >
        <i class="fa fa-bars" style="font-size: 1.3rem;color: #333;"></i>
    </a>
    <header class="navBar">
        <div class="navInner">
            <a href="/">
                <img src="/Images/docNavLogo.png" alt="">
            </a>
            <a class="navBar-menu-button" href="javascript:;">
                <i class="fa fa-bars" style="font-size: 1.3rem;color: #333;"></i>
            </a>
            <div class="navInnerRight">
                <div class="navItem lang-select">
                    <div class="dropdown-wrapper">
                        <button type="button" aria-label="Select language" class="dropdown-title">
                            <span class="title">Language</span> <span class="arrow right"></span>
                        </button>
                        <ul class="nav-dropdown" style="display: none;">
                            <li class="dropdown-item">
                                <a href="javascript:void(0)" data-lang="" class="nav-link lang-change">En</a>
                            </li>
                            <li class="dropdown-item">
                                <a href="javascript:void(0)" data-lang="" class="nav-link lang-change">Cn</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <aside class="sideBar">{$sideBar}</aside>
    <section class="mainContent">
        <div class="content markdown-body">{$page.html}</div>
        <div class="right-menu" id="right-menu"></div>
    </section>
</div>
<script>
    (function ($) {
        var container = $('.container');
        $('.sideBar a').on('click', function () {
            container.removeClass('layout-2');
            container.addClass('layout-1');
        });
        var changeLayout = function () {
            if (container.hasClass('layout-1')) {
                container.removeClass('layout-1');
                container.addClass('layout-2');
            } else {
                container.removeClass('layout-2');
                container.addClass('layout-1');
            }
        }
        $('.sideBar-toggle-button, .navBar-menu-button').on('click', changeLayout);
    })(jQuery);
</script>
<script>
    hljs.initHighlightingOnLoad();
    $(function () {

        $.each($('.sideBar li:has(li)'), function () {
            // var data = $(this).append( 'asd');
            $(this).attr('isOpen', 0).addClass('fa fa-angle-right');
        });

        $('.sideBar li:has(ul)').click(function (event) {
            if (this == event.target) {
                $(this).children().toggle('fast');
                if ($(this).attr('isOpen') == 1) {
                    $(this).attr('isOpen', 0);
                    $(this).removeClass('fa fa-angle-down');
                    $(this).addClass('fa fa-angle-right');
                } else {
                    $(this).attr('isOpen', 1);
                    $(this).removeClass('fa fa-angle-right');
                    $(this).addClass('fa fa-angle-down');
                }
            }
        });

        // 自动展开菜单父级
        $.each($('.sideBar ul li a'), function () {
            $(this).filter("a").css("text-decoration", "none").css('color', '#2c3e50');
            if ($(this).attr('href') === window.location.pathname) {
                $(this).filter("a").css("text-decoration", "underline").css('color', '#0080ff');
                var list = [];
                var parent = this;
                while (1) {
                    parent = $(parent).parent();
                    if (parent.hasClass('sideBar')) {
                        break;
                    } else {
                        parent.click();
                    }
                }
            }
        });

        // 切换中英文
        $('.lang-select').mouseover(function (e) {
            $('.nav-dropdown').toggle();
        });
        $('.lang-select').mouseout(function (e) {
            $('.nav-dropdown').toggle();
        });

        // 拦截菜单点击事件切换右侧内容
        $('.sideBar ul li a').on('click', function () {
            $.each($('.sideBar ul li a'), function () {
                $(this).filter("a").css("text-decoration", "none").css('color', '#2c3e50');
            });
            $(this).filter("a").css("text-decoration", "underline").css('color', '#0080ff');
            var href = $(this).attr('href');
            $.ajax({
                url: href,
                method: 'POST',
                success: function (res) {
                    window.history.pushState(null, null, href);
                    var newHtml = $(res);
                    document.title = newHtml.filter('title').text();
                    var metaList = ['keywords', 'description'];
                    for (var i in metaList) {
                        var col = metaList[i];
                        var newVal = newHtml.filter('meta[name=' + col + ']').attr('content');
                        if (!newVal) {
                            newVal = '';
                        }
                        $('meta[name="' + col + '"]').attr("content", newVal);
                    }
                    $('.markdown-body').html(newHtml.find('.markdown-body').eq(0).html());
                    hljs.initHighlighting.called = false;
                    hljs.initHighlighting();
                    window.scrollTo(0, 0);
                    renderRightMenu();
                }
            });
            return false;
        });

        // 本章详情
        renderRightMenu();
    });

    function dragFunc(id) {
        var Drag = document.getElementById(id);
        Drag.onmousedown = function (event) {
            var ev = event || window.event;
            event.stopPropagation();
            var disX = ev.clientX - Drag.offsetLeft;
            var disY = ev.clientY - Drag.offsetTop;
            document.onmousemove = function (event) {
                var ev = event || window.event;
                Drag.style.left = ev.clientX - disX + "px";
                Drag.style.top = ev.clientY - disY + "px";
                Drag.style.cursor = "move";
            };
        };
        Drag.onmouseup = function () {
            document.onmousemove = null;
            this.style.cursor = "default";
        };
    }

    // ***右侧本章节导航**
    function renderRightMenu() {
        var rightMenu = [];
        $(".markdown-body").children().each(function (index, element) {
            var tagName = $(this).get(0).tagName;
            if (tagName.substr(0, 1).toUpperCase() == "H") {
                var contentH = $(this).text();//获取内容
                var markid = "mark-" + tagName + "-" + index.toString();
                $(this).attr("id", contentH);//为当前h标签设置id
                var level = tagName.substr(1, 2);
                rightMenu.push({
                    level: level,
                    content: contentH,
                    markid: markid,
                });
            }
        });
        $('.right-menu').empty();
        $('.right-menu').append("<div class='title'><i class='fa fa-list'></i> 本章导航</div>");
        $.each(rightMenu, function (index, item) {
            var padding_left = (item.level - 1) * 12 + "px";
            $('.right-menu').append("<li style='padding-left:" + padding_left + "'><a href='#" + item.content + "' class='right-menu-item'>" + item.content + "</a></li>");
        });
        // 防止点击的导航是最底部，拉取滑动的只会到倒数其他菜单
        $('.right-menu').on('click', 'a', function () {
            // 延迟执行 等滚动完
            var that = $(this);
            setTimeout(function (that) {
                $(".right-menu-item.active").removeClass("active");
                that.addClass("active");
            }, 50, that);
        });
        // 切换导航显示
        $('.right-menu .title').on('click', function () {
            $(this).siblings().toggle();
        });
        dragFunc("right-menu");
    }
</script>
</body>
</html>
