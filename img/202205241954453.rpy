define mc = Character('[pcname]' , color= "#4a55ef")
define m = Character('[stepmomname]' , color= "#ffc8ff")
define s = Character('[stepsisname]' , color= "#ff1c63")
define ms = Character('Marcus', color= "#bc0000")
define l = Character('Lucas' , color= "#26700c")
define li = Character('Liza' , color= "#9b3333")
define e = Character('Elena' , color= "#844d0a")
define so = Character('Sophie' , color= "#2eff93")
define j = Character( 'James' , color= "#2eff22" )
define ad = Character('Adien' , color= "#2eff59" )
define em = Character('Emma' , color= "#9b3333")
define w = Character('Mr. Watson' , color= "#2eff12")
define ma = Character('Mary' , color= "#2eff01")
define je = Character('Jerome' , color= "#2eff56")
define se = Character('Selena' , color= "#5D6D7E")
define k = Character('Karla' , color= "#2874A6")
define de = Character('Developer' , color= "#844d0a")
define me = Character('Maude' , color= "#5D6D7E")
define le = Character('Mrs. Felinez' , color= "#2762CB")
define xi = Character('Xi Yan' , color= "#E7DE14")
define es = Character('Dr. Rai' , color= "#701470")
define ri = Character('Ricardo' , color= "#AB792F")
define ka = Character('Kate' , color= "#CDD106")
define cl = Character('Clarissa' , color= "#8B4CA2")
define an = Character('Annie' , color= "#FF82D9")
define fr = Character('Franklin' , color= "#1A7B2A")
define we = Character('Weiku' , color= "#F1401B")
define kl = Character('Kana' , color= "#8AE424")
define ba = Character('Barbara' , color= "#5D6D7E")
define xsr = Character('XXSILENT_RUNNINGXX', color="#5D6D7E")
define nr = Character('Nurse Rose', color= "#5D6D7E")
define tr = Character('Triss', color= "#5D6D7E")
define la = Character('Laura', color= "#5D6D7E")
define isa = Character('Isabella', color= "#5D6D7E")
define ha = Character('Hannah', color= "#8B4CA2")
define mic = Character("Micheal", color= "#F1401B")
define gr = Character("Grant", color= "#F1401B")

init:
    $ lt = Position(xpos=0.30)
    $ rt = Position(xpos=0.70)
    $ lt2 = Position(xpos=0.01, xanchor=1, ypos=1, yanchor=1)
    $ rt2 = Position(xpos = 0.75, xanchor=1, ypos=1, yanchor=1)

define flash = Fade(0.1, 0.0, 0.5, color="#fff")
define fadehold = Fade(0.5, 1.0, 0)


define quick_dissolve = Dissolve(0.2, hard=True)

define medium_dissolve = Dissolve(1.5, hard=True)

define slow_dissolve = Dissolve(3.0, hard=True)


image icon = "icon.png"
image grey = "grey screen.png"


image splash = "pre.jpg"

label splashscreen:
    scene black
    with Pause(2)

    show splash with dissolve
    with Pause(6)

    return



image day1bang:
    "day1nbang2"
    pause 0.7
    "day1nbang3"
    pause 0.7
    repeat

image bg sophieblowmarcus:
    "day3epartyblow3"
    pause 0.7
    "day3epartyblow4"
    pause 0.7
    repeat

image bg mchandjob:
    "day3epartyhj7"
    pause 0.7
    "day3epartyhj8"
    pause 0.7
    repeat

image privacy:
    "day0nprivacy2"
    pause 0.7
    "day0nprivacy3"
    pause 0.7
    repeat

label start:
    scene black
    $ initStats()
    jump intro

label after_load:
    $ initStats()
    if DayNr >= 11:
        python:
            for i in [m.name, s.name, "Karla", "Emma", "Maude", "Sophie", "Liza", "Marcus", "James", "Weiku", "Lucas", "Annie", "Clarissa", "Elena", "Dr Rai", "Mrs Felinez", "Xi Yan"]:
                charDict[i].unlocked = True
    return
# Decompiled by unrpyc: https://github.com/CensoredUsername/unrpyc
