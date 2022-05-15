screen inventoryscreen():
    modal True
    hbox xalign 0.5 yalign 0.8:
        imagebutton:
            idle "screens_update/blank_screen.webp"
    hbox xalign 0.5 yalign 0.05:
        text "{size=200}Inventory" outlines [ (absolute(2), "#000", absolute(0), absolute(1)) ] color "#FFF" font "Timeless.ttf" xalign 0.5
    hbox xalign 0.97 yalign 0.03:
        imagebutton:
            idle "icons_update/icon_relations.webp" hover "icons_update/icon_relations_hover.webp" action [Show("statusscreen"), Hide("inventoryscreen")]
    hbox xalign 0.91 yalign 0.03:
        imagebutton:
            idle "icons_update/icon_map.webp" hover "icons_update/icon_map_hover.webp" action [Show("mapscreen"), Hide("inventoryscreen")]
    hbox xalign 0.85 yalign 0.03:
        imagebutton:
            idle "icons_update/icon_quest.webp" hover "icons_update/icon_quest_hover.webp" action [Show("questscreen"), Hide("inventoryscreen")]
    hbox xalign 0.79 yalign 0.03:
        imagebutton:
            idle "icons_update/icon_bag.webp" hover "icons_update/icon_bag_hover.webp" action [Show("status01"), Hide("inventoryscreen")]

    hbox xalign 0.5 yalign 0.40 spacing 15:
        imagebutton:
            idle "screens_inventory/flowers_r_icon.webp"
        imagebutton:
            idle "screens_inventory/flowers_sr_icon.webp"
        imagebutton:
            idle "screens_inventory/flowers_ssr_icon.webp"
        imagebutton:
            idle "screens_inventory/flowers_ssg_icon.webp"
        imagebutton:
            idle "screens_inventory/junk_inventory_icon.webp"






    textbutton "[bl][flowers_r]" xalign 0.2825 yalign 0.345
    textbutton "[bl][flowers_sr]" xalign 0.4095 yalign 0.345
    textbutton "[bl][flowers_ssr]" xalign 0.537 yalign 0.345
    textbutton "[bl][flowers_ssg]" xalign 0.664 yalign 0.345
    textbutton "[bl][junkinventory]" xalign 0.7925 yalign 0.345

    hbox xalign 0.5 yalign 0.75 spacing 15:
        imagebutton:
            idle "screens_inventory/magicflower_icon.webp"
        imagebutton:
            idle "screens_inventory/parfait_icon.webp"
        imagebutton:
            idle "screens_inventory/plushie_icon.webp"
        imagebutton:
            idle "screens_inventory/book_icon.webp"

    textbutton "[bl][inventory_magicflower]" xalign 0.347 yalign 0.643
    textbutton "[bl][inventory_parfait]" xalign 0.474 yalign 0.643
    textbutton "[bl][inventory_plushie]" xalign 0.600 yalign 0.643
    textbutton "[bl][inventory_book]" xalign 0.726 yalign 0.643

screen flower_rates_screen():
    hbox xpos 0.77 ypos 0.69:
        imagebutton:
            idle "screens_inventory/flower_rates_screen.webp"
    textbutton "{size=25}Rare Flowers - $[flower_prices_r]/each (Avg: $25)" xpos 0.8825 ypos 0.76 style "flowerrates"
    textbutton "{size=25}SR Flowers - $[flower_prices_sr]/each (Avg: $97)" xpos 0.8825 ypos 0.80 style "flowerrates"
    textbutton "{size=25}SSR Flowers - $[flower_prices_ssr]/each (Avg: $480)" xpos 0.8825 ypos 0.84 style "flowerrates"
    textbutton "{size=25}SSGSS Flowers - $[flower_prices_ssg]/each (Avg: $1800)" xpos 0.8825 ypos 0.88 style "flowerrates"
    textbutton "{size=25}Magical Flower - $[magicflower_price]/each (Avg: $300)" xpos 0.8825 ypos 0.92 style "flowerrates"



label street01_trashcan_label:
    hide screen status01

    $ junkinventory_rng = 0

    if (junkinventory > 99):
        "You can't carry any more junk..."
    elif (junk_street01):
        "There's nothing inside worth taking..."
    elif True:
        $ junk_street01 = True
        $ junkinventory_rng += renpy.random.randint(1,7)
        $ junkinventory += junkinventory_rng
        play sound "audio/flowers_r.mp3" volume 2
        show junk_icon at up_happy
        "You found [yel][junkinventory_rng]{/color} pieces of junk!"

    jump streetlabel_01

label street03_trashcan_label:
    hide screen status01

    $ junkinventory_rng = 0

    if (junkinventory > 99):
        "You can't carry any more junk..."
    elif (junk_street03):
        "There's nothing inside worth taking..."
    elif True:
        $ junk_street03 = True
        $ junkinventory_rng += renpy.random.randint(1,7)
        $ junkinventory += junkinventory_rng
        play sound "audio/flowers_r.mp3" volume 2
        show junk_icon at up_happy
        "You found [yel][junkinventory_rng]{/color} pieces of junk!"

    jump streetlabel_03

label street04_trashcan_label:
    hide screen status01

    $ junkinventory_rng = 0

    if (junkinventory > 99):
        "You can't carry any more junk..."
    elif (junk_street04):
        "There's nothing inside worth taking..."
    elif True:
        $ junk_street04 = True
        $ junkinventory_rng += renpy.random.randint(1,7)
        $ junkinventory += junkinventory_rng
        play sound "audio/flowers_r.mp3" volume 2
        show junk_icon at up_happy
        "You found [yel][junkinventory_rng]{/color} pieces of junk!"

    jump streetlabel_04
# Decompiled by unrpyc: https://github.com/CensoredUsername/unrpyc
