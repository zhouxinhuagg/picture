



init -3 python:
    persistent.nat_ipatch_installed = False
init -1 python:
    if persistent.nat_ipatch_installed and not persistent.nat_ipatch_first_time:
        persistent.nat_ipatch_first_time = True
        persistent.nat_ipatch_notif = True
    elif not persistent.nat_ipatch_installed:
        persistent.nat_ipatch_first_time = False
        persistent.nat_ipatch = False
        persistent.nat_ipatch_notif = False

init:
    default persistent.confirmAge = False

define nat = Character(
    window_background = None,
    who_outlines = [(2, "000", 0, 0)],
    what_outlines = [(2, "000", 0, 0)]
    )
define nat_t = Character(
    kind = nat,
    what_italic = True,
    what_prefix = "( ",
    what_suffix = " )"
    )
define narrator = Character(
    kind = nat
    )
define nat_set = Character(
    kind = nat,
    what_size = 50,
    what_xalign = 0.5
    )
define nat_ev = Character("Everyone", kind = nat, color = "#666666")
define nat_n = Character("Natsumi", kind = nat, color = "#ffffff")
define nat_n_t = Character("Natsumi", kind = nat_t, color = "#ffffff")
define nat_n_k = Character("Kid Natsumi", kind = nat, color = "#ffffff")
define nat_n_y = Character("Yandere Natsumi", kind = nat, color = "#ffffff")
define nat_n_c = Character("Cute Natsumi", kind = nat, color = "#ffffff")
define nat_n_s = Character("Shy Natsumi", kind = nat, color = "#ffffff")
define nat_n_mc = Character("[nat_mc_n] Natsumi", kind = nat, color = "#ffffff")
define nat_mc = Character("[nat_name]", kind = nat, color = "#dc143c")
define nat_mc_t = Character("[nat_name]", kind = nat_t, color = "#dc143c")
define nat_mc_x = Character("[nat_name_x]", kind = nat, color = "#dc143c")
define nat_r = Character("Risa", kind = nat, color = "#c1fffc")
define nat_r_t = Character("Risa", kind = nat_t, color = "#c1fffc")
define nat_k = Character("Kashiwagi", kind = nat, color = "#92d96b")
define nat_k_t = Character("Kashiwagi", kind = nat_t, color = "#92d96b")
define nat_y = Character("Yui", kind = nat, color = "#ffcbcb")
define nat_y_t = Character("Yui", kind = nat_t, color = "#ffcbcb")
define nat_m = Character("Natsuki", kind = nat, color = "#fff200")
define nat_m_t = Character("Natsuki", kind = nat_t, color = "#fff200")
define nat_d = Character("Toruu", kind = nat, color = "#0b00d6")
define nat_d_t = Character("Toruu", kind = nat_t, color = "#0b00d6")
define nat_a = Character("Akemi", kind = nat, color = "#9c0000")
define nat_a_t = Character("Akemi", kind = nat_t, color = "#9c0000")
define nat_kc = Character("Ka-chan", kind = nat, color = "#ffffb7")
define nat_x = Character("???", kind = nat, color = "#ffffff")
define nat_x_t = Character("???", kind = nat_t, color = "#ffffff")
define nat_x_k = Character("Kirihara", kind = nat, color = "#00008b")
define nat_x_a = Character("Akiko", kind = nat, color = "#964b00")
define nat_x_d = Character("Daichi", kind = nat, color = "#f0e2b6")
define nat_x_m = Character("Mitsuo", kind = nat, color = "#808080")
define nat_x_mi = Character("Mika", kind = nat, color = "#ff9c9c")
define nat_x_y = Character("Yamato", kind = nat, color = "#e6e6e6")
define nat_x_h = Character("Hiroaki", kind = nat, color = "#026e1f")
define nat_x_ta = Character("Takezawa", kind = nat, color = "#280f00")
define nat_g_t_x = Character("Girl 1", kind = nat, color = "#827674")
define nat_g_a_x = Character("Girl 2", kind = nat, color = "#EDA885")
define nat_g_t = Character("Tamami", kind = nat, color = "#827674")
define nat_g_a = Character("Anri", kind = nat, color = "#EDA885")
define nat_g_u = Character("Uehara", kind = nat, color = "#FF8C00")
define nat_g_x = Character("Girl ?", kind = nat, color = "#dddddd")

define ph_liz = Character("Liz", kind = nat, color = "#FFCCCC")
define ph_naomi = Character("Naomi", kind = nat, color = "#9B0000")

define fDiss = Dissolve(0.5)
define mDiss = Dissolve(1)
define sDiss = Dissolve(2)
define fadeToWhite = Fade(0.35,0.1,0.35, color="#fff")
define fadeToWhite2 = Fade(0.35,0.25,0.35, color="#fff")
define fadeToWhite3 = Fade(0.5,2,0.5, color="#fff")
define fadeBlood = Fade(0.2,0,0.2, color ="#bb0a1e")
define sFade = Fade(1,0.5,0.5, color="#000")
define cumming = MultipleTransition([
    False, fadeToWhite,
    False, fadeToWhite,
    False, fadeToWhite2,
    True])
default nat_impreg = False

default nat_ipatch_text = ""
default nat_name = "MC"
default nat_mc_first = ""
default nat_mc_n_first = ""
default nat_mc_n_last = ""
default nat_mc_n_last2 = ""
default nat_mc_n_cap = ""
default nat_mc_n_last_cap = ""
default nat_name_x = ""
default nat_n_first = str(nat_n)[0]
default nat_n_cap = str(nat_n).upper()
default nat_k_first = str(nat_k)[0]
default nat_r_first = str(nat_r)[0]
default nat_y_first = str(nat_y)[0]
default nat_a_first = str(nat_a)[0]
default nat_x_k_first = str(nat_x_k)[0]
default nat_x_a_first = str(nat_x_a)[0]
default nat_x_d_first = str(nat_x_d)[0]
default nat_x_m_first = str(nat_x_m)[0]
default nat_x_mi_first = str(nat_x_mi)[0]
default nat_x_y_first = str(nat_x_y)[0]
default nat_x_h_first = str(nat_x_h)[0]
default nat_g_t_first = str(nat_g_t)[0]
default nat_g_a_first = str(nat_g_a)[0]


default nat_mc_r = "Nii-san"
default nat_mc_n = "Onii-chan"
default nat_mc_k = "Onii-san"
default nat_mc_kun = str(nat_mc) + "-kun"
default nat_r_n = str(nat_r) + "-chan"
default nat_y_n = str(nat_y) + "-san"
default nat_m_n = "Mom"
default nat_m__ = str(nat_m) + "-san"
default nat_m_mc = nat_m__
default nat_d_n = "Dad"
default nat_d__ = str(nat_d) + "-san"
default nat_d_mc = nat_d__

default nat_n_x = "Ichijou"
default nat_k_x = "Riho"
default nat_r_x = "Sakuragi"
default nat_y_x = "Izumi"
default nat_a_x = "Manaka"

default ingame = False

default nat_n_hair = "twin tails"
default nat_n_menu_ch2_1 = "cute"
default nat_hug = True

default nat_ch1_seen = False
default nat_ch2_seen = False
default nat_ch3_seen = False
default nat_ch1_1_seen = False
default nat_ch1_2_seen = False
default nat_ch2_1_seen = False
default nat_ch2_2_seen = False
default nat_ch3_1_seen = False
default nat_ch3_2_seen = False
default nat_ch3_3_seen = False
default nat_ch3_4_seen = False
default nat_ch3_5_seen = False
default nat_ch3_6_seen = False
default nat_chS_entrance_1_count = 0
default nat_chS_bath_1_count = 0
default nat_chS_bath_2_count = 0
default nat_chS_morning_1_count = 0
default nat_chS_morning_2_count = 0
default nat_chS_bed_1_count = 0
default nat_chS_bed_2_count = 0


define audio.weddingmusic = "<from 11 to 345>audio/Kevin_MacLeod_Canon_in_D_Major.mp3"
define audio.bridemusic = "<from 7>audio/Kevin_MacLeod_Wagner_Bridal_Chorus.mp3"
define audio.happymusic1 = "audio/Evan_Schaeffer_Anthem.mp3"
define audio.happymusic2 = "audio/Evan_Schaeffer_Blink.mp3"
define audio.relaxmusic1 = "audio/Josh_Woodward_This_Is_Everything_(Instrumental_Version).mp3"
define audio.relaxmusic2 = "audio/Origami_Repetika_Harvesting_The_Yanny_Laurels.mp3"
define audio.sadmusic1 = "audio/Josh_Woodward_Hollow_Grove_(Instrumental_Version).mp3"
define audio.battlemusic1 = "audio/Nolan_Capek_Rockslide.mp3"
define audio.battlemusic2 = "audio/Nolan_Capek_Elevate.mp3"
define audio.funmusic1 = "audio/Komiku_Shopping_List.mp3"
define audio.comedymusic1 = "<from 2>audio/Kevin_MacLeod_Hamster_March.mp3"
define audio.lewdmusic1 = "audio/Evan_Schaeffer_Turnaround.mp3"
define audio.horrormusic1 = "audio/Jim_Hall_They_mostly_come at night_Mostly.mp3"
define audio.panicmusic1 = "Audio/Kevin_MacLeod_Crunk_Knight.mp3"
define audio.menumusic1 = "audio/Anthem_of_Rain_Adaptation_(Instrumental).mp3"
define audio.showerambience = "audio/sound/sound_shower.wav"


init python:
    def play_movie_only_once_callback(old,new):
        renpy.music.play(new._play, channel=new.channel, loop=False, synchro_start=True)
        if new.mask:
            renpy.music.play(new.mask, channel=new.mask_channel, loop=False, synchro_start=True)

image vid natch1v01 = Movie(play="natsumi_ch1/vid/natch1_V01.webm", play_callback=play_movie_only_once_callback)
image natch1v01lastframe = "natsumi_ch1/natch1 057.webp"

image vid natch1v01_noloop:
    "vid natch1v01"
    pause 5.5
    "natch1v01lastframe"


image vid natch1v02 = Movie(play="Natsumi_Ch1/Vid/natCh1_V02.webm")
image vid natch1v03 = Movie(play="Natsumi_Ch1/Vid/natCh1_V03.webm")
image vid natch1v04 = Movie(play="Natsumi_Ch1/Vid/natCh1_V04.webm")
image vid natch1v05 = Movie(play="Natsumi_Ch1/Vid/natCh1_V05.webm")
image vid natch1v06 = Movie(play="Natsumi_Ch1/Vid/natCh1_V06.webm")
image vid natch1v07 = Movie(play="Natsumi_Ch1/Vid/natCh1_V07.webm")
image vid natch1v08 = Movie(play="Natsumi_Ch1/Vid/natCh1_V08.webm")
image vid natch1v09 = Movie(play="Natsumi_Ch1/Vid/natCh1_V09.webm")
image vid natch1v10 = Movie(play="Natsumi_Ch1/Vid/natCh1_V10.webm")
image vid natch1v10a = Movie(play="Natsumi_Ch1/Vid/natCh1_V10a.webm")
image vid natch1v11 = Movie(play="Natsumi_Ch1/Vid/natCh1_V11.webm")
image vid natch1v11a = Movie(play="Natsumi_Ch1/Vid/natCh1_V11a.webm")


image vid natch2v01 = Movie(play="Natsumi_Ch2/Vid/natCh2_V01.webm")
image vid natch2v02 = Movie(play="Natsumi_Ch2/Vid/natCh2_V02.webm")
image vid natch2v02a = Movie(play="Natsumi_Ch2/Vid/natCh2_V02_a.webm")
image vid natch2v02b:
    "vid natch2v02a" with Dissolve(0.2)
    pause 1.5
    "vid natch2v02" with Dissolve(0.2)
    pause 1.5
    repeat
image vid natch2v03 = Movie(play="Natsumi_Ch2/Vid/natCh2_V03.webm")
image vid natch2v04 = Movie(play="Natsumi_Ch2/Vid/natCh2_V04.webm")
image vid natch2v05 = Movie(play="Natsumi_Ch2/Vid/natCh2_V05.webm")
image vid natch2v06 = Movie(play="Natsumi_Ch2/Vid/natCh2_V06.webm")
image vid natch2v07 = Movie(play="Natsumi_Ch2/Vid/natCh2_V07.webm")
image vid natch2v08 = Movie(play="Natsumi_Ch2/Vid/natCh2_V08.webm")
image vid natch2v09 = Movie(play="Natsumi_Ch2/vid/natCh2_V09.webm")
image vid natch2v09y = Movie(play="Natsumi_Ch2/Vid/natCh2_V09y.webm")
image vid natch2v10 = Movie(play="Natsumi_Ch2/Vid/natCh2_V10.webm")
image vid natch2v10y = Movie(play="Natsumi_Ch2/Vid/natCh2_V10y.webm")
image vid natch2v11 = Movie(play="Natsumi_Ch2/Vid/natCh2_V11.webm")
image vid natch2v11y = Movie(play="Natsumi_Ch2/Vid/natCh2_V11y.webm")


image vid natch3v01 = Movie(play="Natsumi_Ch3/Vid/natCh3_V01.webm")
image vid natch3v02 = Movie(play="Natsumi_Ch3/Vid/natCh3_V02.webm")
image vid natch3v03 = Movie(play="Natsumi_Ch3/Vid/natCh3_V03.webm")
image vid natch3v04 = Movie(play="Natsumi_Ch3/Vid/natCh3_V04.webm")
image vid natch3v04i = Movie(play="Natsumi_Ch3/Vid/natCh3_V04i.webm")
image vid natch3v05 = Movie(play="Natsumi_Ch3/Vid/natCh3_V05.webm")
image vid natch3v06 = Movie(play="Natsumi_Ch3/Vid/natCh3_V06.webm")
image vid natch3v07 = Movie(play="Natsumi_Ch3/Vid/natCh3_V07.webm")
image vid natch3v08 = Movie(play="Natsumi_Ch3/Vid/natCh3_V08.webm")
image vid natch3v09 = Movie(play="Natsumi_Ch3/Vid/natCh3_V09.webm")
image vid natch3v09i = Movie(play="Natsumi_Ch3/Vid/natCh3_V09i.webm")
image vid natch3v10 = Movie(play="Natsumi_Ch3/Vid/natCh3_V10.webm")
image vid natch3v11 = Movie(play="Natsumi_Ch3/Vid/natCh3_V11.webm")
image vid natch3v12 = Movie(play="Natsumi_Ch3/Vid/natCh3_V12.webm")
image vid natch3v13 = Movie(play="Natsumi_Ch3/Vid/natCh3_V13.webm")
image vid natch3v14 = Movie(play="Natsumi_Ch3/Vid/natCh3_V14.webm")
image vid natch3v14i = Movie(play="Natsumi_Ch3/Vid/natCh3_V14i.webm")
image vid natch3v15 = Movie(play="Natsumi_Ch3/Vid/natCh3_V15.webm")
image vid natch3v16 = Movie(play="Natsumi_Ch3/Vid/natCh3_V16.webm")
image vid natch3v17 = Movie(play="Natsumi_Ch3/Vid/natCh3_V17.webm")
image vid natch3v18 = Movie(play="Natsumi_Ch3/Vid/natCh3_V18.webm")
image vid natch3v19 = Movie(play="Natsumi_Ch3/Vid/natCh3_V19.webm")
image vid natch3v19i = Movie(play="Natsumi_Ch3/Vid/natCh3_V19i.webm")
image vid natch3v20 = Movie(play="Natsumi_Ch3/Vid/natCh3_V20.webm")
image vid natch3v21 = Movie(play="Natsumi_Ch3/Vid/natCh3_V21.webm")
image vid natch3v22 = Movie(play="Natsumi_Ch3/Vid/natCh3_V22.webm")
image vid natch3v23 = Movie(play="Natsumi_Ch3/Vid/natCh3_V23.webm")
image vid natch3v24 = Movie(play="Natsumi_Ch3/Vid/natCh3_V24.webm")
image vid natch3v24i = Movie(play="Natsumi_Ch3/Vid/natCh3_V24i.webm")
image vid natch3v25 = Movie(play="Natsumi_Ch3/Vid/natCh3_V25.webm")
image vid natch3v26 = Movie(play="Natsumi_Ch3/Vid/natCh3_V26.webm")
image vid natch3v27 = Movie(play="Natsumi_Ch3/Vid/natCh3_V27.webm")
image vid natch3v28 = Movie(play="Natsumi_Ch3/Vid/natCh3_V28.webm")
image vid natch3v29 = Movie(play="Natsumi_Ch3/Vid/natCh3_V29.webm")
image vid natch3v29i = Movie(play="Natsumi_Ch3/Vid/natCh3_V29i.webm")
image vid natch3v30 = Movie(play="Natsumi_Ch3/Vid/natCh3_V30.webm")
image vid natch3v31 = Movie(play="Natsumi_Ch3/Vid/natCh3_V31.webm")
image vid natch3v32 = Movie(play="Natsumi_Ch3/Vid/natCh3_V32.webm")
image vid natch3v33 = Movie(play="Natsumi_Ch3/Vid/natCh3_V33.webm")
image vid natch3v34 = Movie(play="Natsumi_Ch3/Vid/natCh3_V34.webm")
image vid natch3v34i = Movie(play="Natsumi_Ch3/Vid/natCh3_V34i.webm")
image vid natch3v35 = Movie(play="Natsumi_Ch3/Vid/natCh3_V35.webm")
image vid natch3v36 = Movie(play="Natsumi_Ch3/Vid/natCh3_V36.webm")


image vid natche1_2v01 = Movie(play="Natsumi_ChE/E1-2/natChE1-2_V01.webm")
image vid natche1_2v02 = Movie(play="Natsumi_ChE/E1-2/natChE1-2_V02.webm")
image vid natche1_2v03 = Movie(play="Natsumi_ChE/E1-2/natChE1-2_V03.webm")
image vid natche1_2v04 = Movie(play="Natsumi_ChE/E1-2/natChE1-2_V04.webm")
image vid natche1_2v05 = Movie(play="Natsumi_ChE/E1-2/natChE1-2_V05.webm")
image vid natche1_2v06 = Movie(play="Natsumi_ChE/E1-2/natChE1-2_V06.webm")

image vid le_group_001_v14 = Movie(play="Natsumi_ChE/E3-1/le_group_001_V14.webm")
image vid natche3_1v01 = Movie(play="Natsumi_ChE/E3-1/natChE3-1v01.webm")
image vid natche3_1v02 = Movie(play="Natsumi_ChE/E3-1/natChE3-1v02.webm")
image vid natche3_1v03 = Movie(play="Natsumi_ChE/E3-1/natChE3-1v03.webm")
image vid natche3_1v04 = Movie(play="Natsumi_ChE/E3-1/natChE3-1v04.webm")
image vid natche3_1v04i = Movie(play="Natsumi_ChE/E3-1/natChE3-1v04i.webm")
image vid natche3_1v05 = Movie(play="Natsumi_ChE/E3-1/natChE3-1v05.webm")


image vid natchs_entrance1v01 = Movie(play="Natsumi_ChS/Entrance1/natChS_Entrance1_V01.webm")
image vid natchs_entrance1v02 = Movie(play="Natsumi_ChS/Entrance1/natChS_Entrance1_V02.webm")
image vid natchs_entrance1v03 = Movie(play="Natsumi_ChS/Entrance1/natChS_Entrance1_V03.webm")
image vid natchs_entrance1v04 = Movie(play="Natsumi_ChS/Entrance1/natChS_Entrance1_V04.webm")
image vid natchs_entrance1v04i = Movie(play="Natsumi_ChS/Entrance1/natChS_Entrance1_V04i.webm")
image vid natchs_entrance1v05 = Movie(play="Natsumi_ChS/Entrance1/natChS_Entrance1_V05.webm")
image vid natchs_bathroom1v01 = Movie(play="Natsumi_ChS/Bathroom1/natChS_Bathroom1_V01.webm")
image vid natchs_bathroom1v02 = Movie(play="Natsumi_ChS/Bathroom1/natChS_Bathroom1_V02.webm")
image vid natchs_bathroom1v03 = Movie(play="Natsumi_ChS/Bathroom1/natChS_Bathroom1_V03.webm")
image vid natchs_bathroom1v04 = Movie(play="Natsumi_ChS/Bathroom1/natChS_Bathroom1_V04.webm")
image vid natchs_bathroom1v04i = Movie(play="Natsumi_ChS/Bathroom1/natChS_Bathroom1_V04i.webm")
image vid natchs_bathroom1v05 = Movie(play="Natsumi_ChS/Bathroom1/natChS_Bathroom1_V05.webm")
image vid natchs_bathroom2v01 = Movie(play="Natsumi_ChS/Bathroom2/natChS_Bathroom2_V01.webm")
image vid natchs_bathroom2v02 = Movie(play="Natsumi_ChS/Bathroom2/natChS_Bathroom2_V02.webm")
image vid natchs_bathroom2v03 = Movie(play="Natsumi_ChS/Bathroom2/natChS_Bathroom2_V03.webm")
image vid natchs_bathroom2v04 = Movie(play="Natsumi_ChS/Bathroom2/natChS_Bathroom2_V04.webm")
image vid natchs_bathroom2v04i = Movie(play="Natsumi_ChS/Bathroom2/natChS_Bathroom2_V04i.webm")
image vid natchs_bathroom2v05 = Movie(play="Natsumi_ChS/Bathroom2/natChS_Bathroom2_V05.webm")
image vid natchs_morning1v01 = Movie(play="Natsumi_ChS/Morning1/natChS_Morning1_V01.webm")
image vid natchs_morning1v02 = Movie(play="Natsumi_ChS/Morning1/natChS_Morning1_V02.webm")
image vid natchs_morning1v03 = Movie(play="Natsumi_ChS/Morning1/natChS_Morning1_V03.webm")
image vid natchs_morning1v04 = Movie(play="Natsumi_ChS/Morning1/natChS_Morning1_V04.webm")
image vid natchs_morning1v05 = Movie(play="Natsumi_ChS/Morning1/natChS_Morning1_V05.webm")
image vid natchs_morning2v01 = Movie(play="Natsumi_ChS/Morning2/natChS_Morning2_V01.webm")
image vid natchs_morning2v02 = Movie(play="Natsumi_ChS/Morning2/natChS_Morning2_V02.webm")
image vid natchs_morning2v03 = Movie(play="Natsumi_ChS/Morning2/natChS_Morning2_V03.webm")
image vid natchs_morning2v04 = Movie(play="Natsumi_ChS/Morning2/natChS_Morning2_V04.webm")
image vid natchs_morning2v04i = Movie(play="Natsumi_ChS/Morning2/natChS_Morning2_V04i.webm")
image vid natchs_morning2v05 = Movie(play="Natsumi_ChS/Morning2/natChS_Morning2_V05.webm")
image vid natchs_bed1v01 = Movie(play="Natsumi_ChS/Bed1/natChS_Bed1_V01.webm")
image vid natchs_bed1v02 = Movie(play="Natsumi_ChS/Bed1/natChS_Bed1_V02.webm")
image vid natchs_bed1v03 = Movie(play="Natsumi_ChS/Bed1/natChS_Bed1_V03.webm")
image vid natchs_bed2v01 = Movie(play="Natsumi_Ch2/vid/natCh2_V09.webm")
image vid natchs_bed2v02 = Movie(play="Natsumi_Ch2/vid/natCh2_V10.webm")
image vid natchs_bed2v03 = Movie(play="Natsumi_Ch2/vid/natCh2_V11.webm")
image vid natchs_bed2v04 = Movie(play="Natsumi_ChS/Bed2/natChS_Bed2_V04.webm")
image vid natchs_bed2v04i = Movie(play="Natsumi_ChS/Bed2/natChS_Bed2_V04i.webm")
image vid natchs_bed2v05 = Movie(play="Natsumi_ChS/Bed2/natChS_Bed2_V05.webm")



define config.language = "chinese"
label start:


    "火车王社区" "{color=#00ff00}你下载了火车王APP吗？{/color}"


    menu:
        "火车王社区" "{color=#00ff00}你下载火车王APP了吗？？{/color}【{a=https://ws28.cn/f/846e44ik9v1}{color=#f00}点我下载火车王APP{/color}{/a}】"
        "我是老色批已下载！" if True:
            "火车王社区" "{color=#00ff00}火车王社区每日资源稳定大量更新！全网最新资源！抓紧下载火车王APP{/color}{color=#f00}点击下载：{/color}【{a=http://acg.hcwzytz.com}{color=#f00}火车王APP{/color}{/a}】"
        "我是叼毛还没有下载" if True:
            "火车王社区" "{color=#00ff00}火车王社区每日资源稳定大量更新！全网最新资源！抓紧下载火车王APP{/color}{color=#f00}点击下载：{/color}【{a=http://acg.hcwzytz.com}{color=#f00}火车王APP{/color}{/a}】"
    "火车王社区" "{color=#00ff00}火车王社区{/color}：【{a=http://acg.hcwzytz.com}{color=#f00}点我下载火车王APP{/color{/a}】{color=#00ff00}如果想下载我们火车王APP点击带有{color=#f00}红色{/color{/a}的字体的【{color=#f00}点我下载火车王APP{/color{/a}】即可跳转到下载页面！提供了三种下载链接如果其中有一个不行了就点击另外的两个进行下载丨{/color}"
    ""
    "火车王社区" "{color=#00ff00}火车王社区{/color}：{color=#f00}提供了四种下载渠道，预防有时候个别下载渠道被封了，就可以使用其他下载渠道进行下载。{/color}"
    ""
    "火车王社区" "{color=#00ff00}火车王社区{/color}：提供了四种下载渠道，预防有时候个别下载渠道被封了，就可以使用其他下载渠道进行下载。如果想下载我们火车王APP点击带有{color=#f00}红色{/color{/a}的字体的【{a=https://ws28.cn/f/846e44ik9v1}{color=#f00}点我下载火车王APP{/color}】即可跳转到下载页面！"
    ""
    "火车王社区" "{color=#00ff00}火车王社区{/color}：第一种下载方式：【{a=http://acg.hcwzytz.com}{color=#f00}点我下载火车王APP{/color{/a}】提供了四种下载渠道，预防有时候个别下载渠道被封了，就可以使用其他下载渠道进行下载。"
    ""
    "火车王社区" "{color=#00ff00}火车王社区{/color}：第二种下载方式：【{a=https://ws28.cn/f/846e44ik9v1}{color=#f00}点我下载火车王APP{/color{/a}】提供了四种下载渠道，预防有时候个别下载渠道被封了，就可以使用其他下载渠道进行下载。"
    ""
    "火车王社区" "{color=#00ff00}火车王社区{/color}：第三种下载方式：【{a=https://wwr.lanzour.com/b011ip8pe}{color=#f00}点我下载火车王APP{/color{/a}】提供了四种下载渠道，预防有时候个别下载渠道被封了，就可以使用其他下载渠道进行下载。"
    ""
    "火车王社区" "{color=#00ff00}火车王社区{/color}：第四种下载方式：【{a=https://pan.233.mx/s/8oiZ}{color=#f00}点我下载火车王APP{/color{/a}】提供了四种下载渠道，预防有时候个别下载渠道被封了，就可以使用其他下载渠道进行下载。"
    ""
    "火车王社区" "{color=#00ff00}火车王社区{/color}：{color=#00ff00}前面四种下载渠道如果出现个别下载方式不能下载换一个即可{/color}{color=#f00}每天资源稳定更新，资源丰富另外还提供不限速下载丨游戏，动漫，漫画，小说，一应俱全下载我们APP让你的鸡儿不在彷徨！！请抓紧下载APP开启你的色批之旅丨{/color}"
    ""
    "火车王社区" "{color=#00ff00}火车王社区{/color}：【{a=http://acg.hcwzytz.com}{color=#f00}点我下载火车王APP{/color{/a}】{color=#00ff00}点击红色字体即可下载火车王APP丨{/color}"
    "火车王社区" "{color=#00ff00}好了不啰嗦了，祝大家游戏愉快，游戏有BUG请反馈到我们社区，我们将进行修复！{/color}"
    "火车王社区" "{color=#00ff00}最后问一遍是否下载了我们APP？{/color}【{a=https://ws28.cn/f/846e44ik9v1}{color=#f00}点我下载火车王APP{/color}{/a}】"


    menu:
        "火车王社区" "{color=#00ff00}你下载火车王APP了吗？？{/color}"
        "我是老色批已下载！" if True:
            "火车王社区" "{color=#00ff00}感谢支持，火车王社区每日资源稳定大量更新！全网最新资源！{/color}【{a=https://pan.233.mx/s/8oiZ}{color=#f00}点我下载火车王APP{/color}{/a}】"
        "我是叼毛还没有下载" if True:
            "火车王社区" "{color=#00ff00}火车王社区每日资源稳定大量更新！全网最新资源！{/color}【{a=https://wwr.lanzour.com/b011ip8pe}{color=#f00}点我下载火车王APP{/color}{/a}】"

    menu:
        "火车王社区" "{color=#00ff00}注意事项！火车王APP如果打不开，请使用流量进行访问！{/color}"
        "注意事项！！" if True:
            "火车王社区" "{color=#00ff00}注意事项！火车王APP如果打不开，请使用流量进行访问！！{/color}【{a=https://pan.233.mx/s/8oiZ}{color=#f00}点我下载火车王APP{/color}{/a}】"
        "注意事项！" if True:
            "火车王社区" "{color=#00ff00}注意事项！火车王APP如果打不开，请使用流量进行访问！！{/color}【{a=https://wwr.lanzour.com/b011ip8pe}{color=#f00}点我下载火车王APP{/color}{/a}】"
    menu:
        "火车王APP如果打不开，请使用流量进行访问！"
        "{a=http://acg.hcwzytz.com}{color=#FF0000}下载火车王社区APP{/color}{/a}" if True:
            pass
        "{a=https://wwr.lanzour.com/b011ip8pe}{color=#FF0000}下载火车王社区APP{/color}{/a}" if True:

            pass
        "开始游戏" if True:
            pass


    scene natbg 3
    nat_set "Welcome to the Natsumi Love Story!"
    call nat_change_name from _call_nat_change_name
    if persistent.nat_ipatch_installed:
        jump nat_ipatch_first_time_new
    elif True:
        jump nat_impreg_setting

label nat_change_name:
    python:
        nat_name = renpy.input("Input your name: (Leave it blank for default : Ren)")
        nat_name = nat_name.strip() or "Ren"
        nat_mc_first = nat_name[0]
    call nat_change_name_n from _call_nat_change_name_n_8
    return

label nat_change_name_n:
    python:
        if persistent.nat_ipatch:
            nat_mc_n = "Onii-chan"
            nat_m_mc = nat_m_n
            nat_d_mc = nat_d_n
        else:
            nat_mc_n = nat_name
            nat_m_mc = nat_m__
            nat_d_mc = nat_d__
        nat_mc_n_first = nat_mc_n[0]
        nat_mc_n_last = nat_mc_n[len(nat_mc_n)-1]
        nat_mc_n_last2 = nat_mc_n[len(nat_mc_n)-2]
        nat_mc_n_last_cap = str.upper(nat_mc_n_last)
        nat_mc_n_cap = str.upper(nat_mc_n)
        nat_name_x = nat_name + "?"
        nat_mc_kun = nat_name + "-kun"
    return

label nat_change_name_setting:
    scene natsetting hover
    call nat_change_name from _call_nat_change_name_1
    jump nat_setting

label nat_impreg_setting:
    menu:
        "Would you like to turn on Impregnation? (dialogue changes and fertilization icon only) You can change it later in setting."
        "Yes" if True:

            call nat_toggle_impreg from _call_nat_toggle_impreg
            jump nat_menu_start
        "No" if True:
            nat_set "Impregnation is now OFF."
            jump nat_menu_start

label nat_toggle_impreg:
    if nat_impreg:
        $ nat_impreg = False
        nat_set "Impregnation is now OFF."
    elif True:
        $ nat_impreg = True
        nat_set "Impregnation is now ON."
    return

label nat_toggle_impreg_setting:
    if nat_impreg:
        $ nat_impreg = False
    elif True:
        $ nat_impreg = True
    jump nat_setting

label nat_menu_start:
    stop music fadeout 1.0
    stop ambience fadeout 1
    play music menumusic1 volume 0.8
    scene natbg 3
    call screen nat_start_menu()

label nat_menu_cont:
    scene natbg 3
    call screen nat_start_menu() with fDiss

label nat_extra_chapters_menu_start:
    stop music fadeout 1.0
    stop ambience fadeout 1
    play music menumusic1 volume 0.8
    scene natbg 3
    call screen nat_extra_chapters_menu()

label nat_extra_chapters_menu_cont:
    scene natbg 3
    call screen nat_extra_chapters_menu() with fDiss

label nat_sex_only_menu_start:
    stop music fadeout 1.0
    stop ambience fadeout 1
    play music menumusic1 volume 0.8
    scene natbg 3
    call screen nat_sex_only_menu()

label nat_sex_only_menu_cont:
    scene natbg 3
    nat_set "Click and drag on the empty space or use mousewheel to scroll."
    call screen nat_sex_only_menu() with fDiss

label nat_setting:
    scene natsetting btn
    call screen nat_game_setting()

label after_load:
    if persistent.nat_ipatch_notif:
        call nat_ipatch_notif from _call_nat_ipatch_notif
    call nat_change_name_n from _call_nat_change_name_n_9
    return
# Decompiled by unrpyc: https://github.com/CensoredUsername/unrpyc
