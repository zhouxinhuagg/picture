init offset = -2

default niece_points = 10
default daughter_points = 10
default exwife_points = 10
default nika_points = 10
default lance_points = 10
default gloria_points = 10

default niece_neutral = 0
default niece_good = 0
default niece_evil = 0

default mc_name = "Bryan"
default niece_name = "Tilly"
default daughter_name = "Valerie"
default exwife_name = "Stacey"

define niece_to_mc = _("goddaughter")
define mc_to_niece = _("godfather")
define daughter_to_mc = _("ex-stepdaughter")
define brother_to_mc = _("godbrother")
define daughter_to_niece = _("step-cousin")
define mc_to_daughter = _("stepfather")

define true_story = False

default choices = []




default cumin_side_01 = False
default cumin_side_total = 0
default chp06choice03 = 'daughter'
default chp06choice04 = False
default chp07choice04 = False
default chp09staceypromise = False
default chp09valeriesex01 = False
default chp10valeriesex02 = False
default valeriecuminside = 0
default chp11lancechoice01 = False
default chp11gloriasex = False
default chp15defendNika = False
default chp15nikagivein = False
default chp15onlyone = False
default chp16lie = 0


init -99 python:
    game_languages = []

init 99 python:
    game_languages = list(dict.fromkeys(game_languages))
    game_languages.sort()


default persistent.say_window_alpha = 0
default persistent.choice_window_alpha = 0.666
default persistent.quickmenu_alpha = 0.666
default persistent.say_dialogue_kerning = 0

init python:
    def add_points(char, points=1):
        global niece_points, niece_neutral, niece_good, niece_evil, daughter_points, exwife_points, nika_points, lance_points, gloria_points
        if char == 'niece':
            niece_points = max(niece_points + points, 0)
        elif char == 'niece_neutral':
            niece_neutral = max(niece_neutral + points, 0)
        elif char == 'niece_good':
            niece_good = max(niece_good + points, 0)
        elif char == 'niece_evil':
            niece_evil = max(niece_evil + points, 0)
        elif char == 'daughter':
            daughter_points = max(daughter_points + points, 0)
        elif char == 'exwife':
            exwife_points = max(exwife_points + points, 0)
        elif char == 'nika':
            nika_points = max(nika_points + points, 0)
        elif char == 'lance':
            lance_points = max(lance_points + points, 0)
        elif char == 'gloria':
            gloria_points = max(gloria_points + points, 0)
        else:
            raise Exception("Variable for '%s' does not exist!" % char)
# Decompiled by unrpyc: https://github.com/CensoredUsername/unrpyc
