image splash = "gui/loading-screen.jpg"
image mainmenu = "gui/main_menu.webp"
image white = "#fff"

init python:
    def replacement_show(*args, **kwargs):
        renpy.transition(fast_dissolve)
        return renpy.show(*args, **kwargs)
    config.show = replacement_show

    def replacement_hide(*args, **kwargs):
        renpy.transition(fast_dissolve)
        return renpy.hide(*args, **kwargs)
    config.hide = replacement_hide

label splashscreen:
    show splash with dissolve
    $ renpy.pause(2)
    hide splash with dissolve
    pause 0.5
    show mainmenu with fast_dissolve
    return

label start:


    "LSPACG" "是否已加入LSPACG？"


    menu:
        "LSPACG" "是否已加入LSPACG？"
        "我是老色批已加入！" if True:
            "LSPACG" "感谢关注，不愧是真正的色批"
        "我还没有加入" if True:
            "LSPACG" "超多游戏等你来拿，点击进入：{a=http://lspacgfb.com}http://lspacgfb.com{/a}"
    "LSPACG" "主打精翻汉化游戏，动漫动画，绅士的避风港，让你的鸡儿不再彷徨，期待你的加入！！绅士福利一网打尽！发布页：{a=http://lspacgfb.com/}lspacgfb.com{/a}"
    ""
    "我们不保证提供的服务在您当地是适用的，在使用之前请阅读相关条款和当地法律法规，如有违反请立即离开。"
    ""
    "我们立足于新加坡，对全球华人服务，本站包含18+内容，请未成年网友自觉离开！适量游戏有益身心健康，请勿长时间沉迷游戏，注意保护视力并预防近视，保重身体！"


    scene mainmenu

    if debug_mode:
        python:
            temp = [(_('Introduction'), 0)]
            for i in range(16):
                i += 1
                temp += [(_('Chapter') + ' ' + str(i), "%02d" % i)]

            temp = renpy.display_menu(temp, screen="choice2")

        show screen screen_debug

        if temp != 0:
            $ renpy.jump('chapter' + temp)

    jump introduction

    return

label end(next_time_img=None):
    window hide
    $ Darkpy.disable_skip()
    scene black with slow_dissolve

    if next_time_img is not None:
        show screen screen_center_text(_("Next time on [config.name]...")) with fast_dissolve
        pause
        hide screen screen_center_text with dissolve
        $ renpy.scene()
        $ renpy.show(next_time_img, layer='master')
        $ renpy.with_statement(slow_dissolve)
        pause
        scene black with dissolve

    show screen screen_end(_("Thanks for playing the latest version of [config.name]. It's our passion to make great games and content."), save_msg=next_time_img is None) with dissolve
    pause
    pause
    pause
    $ Darkpy.enable_skip()

    return

label chapter(num, title):
    window hide
    $ quick_menu = False
    $ Darkpy.disable_skip()
    scene black with slow_dissolve

    show screen screen_chapter(num, title)
    $ renpy.pause(3, hard=True)
    pause
    hide screen screen_chapter
    $ renpy.pause(1.5, hard=True)

    $ Darkpy.enable_skip()
    $ quick_menu = True
    return
# Decompiled by unrpyc: https://github.com/CensoredUsername/unrpyc
