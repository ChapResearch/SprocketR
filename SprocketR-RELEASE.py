# version of script released on 12/11/15
# used by page_id 2162 and 2164

import bpy
import os
import math
import mathutils
import sys
import json
from mathutils import Vector
from fractions import Fraction
import operator
name=""
teeth=1
pitchDiameter=1
pitch=.25
idNumber=0
ratio=(0,0)
vexBearingHole= False
versaHoles= False
hexShaftHole= False
roundShaftHole= False
tetrixHub= False
tetrixShaft= False
sprocketArray=[]
var_pi = 3.14
thickness = .12
arc_little = .5
arc_big = .5

def setPitch(temp):
    global pitch
    pitch=temp
    return

def setVexBearingHole(temp):
    global vexBearingHole
    vexBearingHole=temp
    return

def setVersaHoles(temp):
    global versaHoles
    versaHoles=temp
    return

def setHexShaftHole(temp):
    global hexShaftHole
    hexShaftHole=temp
    return

def setRoundShaftHole(temp):
    global roundShaftHole
    roundShaftHole=temp
    return

def setTetrixHub(temp):
    global tetrixHub
    tetrixHub=temp
    return

def setTetrixShaft(temp):
    global tetrixShaft
    tetrixShaft=temp
    return

def setName(teeth):
    global name
    global idNumber
    name ="T%dN%d.stl"%(teeth,idNumber)
    idNumber=idNumber+1
    return

def setRatio(tempRatio):
    global ratio
    ratio=tempRatio
    return

def setTeeth(tempTeeth):
    global teeth
    teeth=tempTeeth
    return

def setOrigin(origo):
    origin = Vector(origo)
    return 

def setPitchDiameter(teeth):
    pi=3.1415
    pitchDiameter= (pitch/(math.sin((pi/teeth))))
    return

def getPitchDiameter(teeth):
    pi=3.1415
    pitchDiameter= (pitch/(math.sin((pi/teeth))))
    return pitchDiameter

def getArcLengthMinor(dia_one, dia_two, CTC):
    if dia_one > dia_two:
        dia_big = dia_one
        dia_little = dia_two
    else: 
        dia_little = dia_one
        dia_big = dia_two
    arc_minor = (var_pi - 2 * math.asin((.5 * dia_big - .5 * dia_little)/CTC))/(2 * var_pi)
    #print("getArcLengthMinor")
    #print(arc_minor)
    return arc_minor


'''
def simplify_fraction(numer, denom):

    if denom == 0:
        return "Division by 0 - result undefined"

    # Remove greatest common divisor:
    common_divisor = gcd(numer, denom)
    (reduced_num, reduced_den) = (numer / common_divisor, denom / common_divisor)
    # Note that reduced_den > 0 as documented in the gcd function.
    ''
    if reduced_den == 1:
        return (numer, denom, reduced_num)
    ''
    if common_divisor == 1:
        return (numer, denom)
    else:
        return (reduced_num, reduced_den)
'''


def createMeshFromData(name, origin, verts, faces):
    # Create mesh and object
    me = bpy.data.meshes.new(name+'Mesh')
    ob = bpy.data.objects.new(name, me)
    ob.location = origin
    ob.show_name = True
 
    # Link object to scene and make active
    scn = bpy.context.scene
    scn.objects.link(ob)
    scn.objects.active = ob
    ob.select = True
 
    # Create mesh from given verts, faces.
    me.from_pydata(verts, [], faces)
    # Update mesh with new data
    me.update()    
    return ob

def getAmplitude(teeth):
    pitch=.25
    pi=3.1415
    pitchDiameter= (pitch/(math.sin((pi/teeth))))
    outerDiameter= (pitch*(0.6+1/math.tan((pi/teeth))))
    #amplitude=(outerDiameter-pitchDiameter)/2
    #pitchDiameter= math.sqrt(pitchDiameter/pi)
    #pitchDiameter= teeth*((pitch*pi*alpha)/(360*math.sin((alpha/2))))
    return pitchDiameter-outerDiameter

def processChainNum(number):
    global thickness, pitch
    (junk,digits) = number.split("n")
    scale_dig = float(digits[0])
    pitch = scale_dig/8
    setPitch(pitch)
    print(pitch)
    type_dig = float(digits[1])
    if type_dig == 5:
        thickness = (scale_dig/16) * .98
        print(thickness)
    return

def getLengthList(picthDiameter, picthDiametertwo, P, A):
    hypotenuseLength = math.sqrt((.5*picthDiametertwo - .5*picthDiameter)**2 + (.5*picthDiameter + .5*picthDiametertwo + (A*2))**2)
    length = 2*hypotenuseLength + (.5 * picthDiameter * 3.14159) + (.5 * picthDiametertwo * 3.14159)
    lengthRounded = math.floor(length)
    diffrence = length-lengthRounded
    length = lengthRounded + math.ceil(4*diffrence) * .25
    lengthList={}
    for i in range(0, 50):
        length = length + P
        lengthList.append(length)
    return lengthList

def getLength(picthDiameter, picthDiametertwo, P, A, CTC):
    tCTC = CTC * 0.0393701
    hypotenuseLength = math.sqrt(((.5*picthDiametertwo - .5*picthDiameter)**2 + (tCTC)**2))
    #print(hypotenuseLength*25.4)
    length = 2*hypotenuseLength + (getArcLengthMinor(picthDiameter, picthDiametertwo, tCTC) * picthDiameter * 3.14159) + ((1-getArcLengthMinor(picthDiameter, picthDiametertwo, tCTC)) * picthDiametertwo * 3.14159)
    length = length * 25.4
    #print(length)
    return  length

def getSlack(pitchDiameter, pitchDiametertwo, CTC):
    A = .13333/2
    hypotenuseLength = math.sqrt((.5*pitchDiametertwo - .5*pitchDiameter)**2 + (.5*pitchDiameter + .5*pitchDiametertwo + (A*2))**2)
    length = 2*hypotenuseLength + (getArcLengthMinor(pitchDiameter, pitchDiametertwo, CTC) * pitchDiameter * 3.14159) + ((1-getArcLengthMinor(pitchDiameter, pitchDiametertwo, CTC)) * pitchDiametertwo * 3.14159)
    print("getSlack")
    print(CTC)
    '''
    lengthRounded = 2*hypotenuseLength + (.5 * picthDiameter * 3.14159) + (.5 * picthDiametertwo * 3.14159)
    lengthRoundedUp = lengthRounded + math.ceil(4*diffrence)
    slack = lengthRoundedUp -length
    '''
    slack = pitch - ( length % pitch )
    return  slack

def getSudoSlack(length,pitch,error):
    length = getLength(picthDiameter, picthDiametertwo, P, A, CTC)
    length = length / 25.4
    over_under = length
    while length > 0: 
        over_under = over_under - pitch
    if over_under > error:
        sudo_slack = over_under - error
    elif over_under < error:
        sudo_slack = error - over_under
    else:
        sudo_slack = 0
    return sudo_slack

def getCenterToCenterList(pitchDiameter, pitchDiametertwo, P, A,dslack,CTC):
    CTC_C = CTC
    #print(pitchDiameter)
    #print(pitchDiametertwo)
    print(getArcLengthMinor(pitchDiameter, pitchDiametertwo, CTC_C))
    hypotenuseLength = math.sqrt((.5*pitchDiametertwo - .5*pitchDiameter)**2 + (CTC_C)**2)
    length = 2 * hypotenuseLength + (getArcLengthMinor(pitchDiameter, pitchDiametertwo, CTC_C) * pitchDiameter * 3.14159) + (1.0 - getArcLengthMinor(pitchDiameter, pitchDiametertwo, CTC_C)) * pitchDiametertwo * 3.14159
    print(length%pitch)
    if dslack==0:
        length = length + (pitch - (length % pitch))
    else:
        length = (((length * (dslack/100 +1))) + (pitch - ((length * ( dslack/100 + 1 ) )%pitch)))
        #desired slack abs error 
    print(length)
    CTClist=[]
    #varies the range 50mm
    for i in range(0, 10):
        if i > 1:
            length = length + pitch
        if(dslack==0):
            hypotenuseLength = .5 * ( length - (getArcLengthMinor(pitchDiameter, pitchDiametertwo, CTC_C) * pitchDiameter * 3.14159) - ((1.0 - getArcLengthMinor(pitchDiameter, pitchDiametertwo, CTC_C)) * pitchDiametertwo * 3.14159))
        else:
            hypotenuseLength = .5 * ( length - (length - (length/(dslack/100 + 1))) - (getArcLengthMinor(pitchDiameter, pitchDiametertwo, CTC_C) * pitchDiameter * 3.14159) - ((1.0 - getArcLengthMinor(pitchDiameter, pitchDiametertwo, CTC_C)) * pitchDiametertwo * 3.14159))
        CTC_C = math.sqrt((hypotenuseLength)**2 - (.5*pitchDiametertwo - .5*pitchDiameter)**2)
        links = length / pitch
        CTClist.append((CTC_C,length,links))
    return CTClist

def findOptimalRatio(ratio,CTC,dslack,errorA):
    global sprocketArray
    setRatio(ratio)
    tratio=getFirstRatio(ratio)
    addAllSetsInRatio(tratio,CTC,dslack,errorA)
    #sprocketArray = sorted(sprocketArray, key= sprocketArray(terror))
    return

def findSideRatio(ratio,side,CTC,dslack,errorA):
    global sprocketArray
    setRatio(ratio)
    (tlittle,tbig)=ratio
    tratio = (tlittle*side,tbig*side)
    findAllSetsInRatio(tratio,CTC,dslack,errorA)
    #sprocketArray = sorted(sprocketArray, key= sprocketArray(terror))
    return

def findOptimalCTC(ratio,CTC,dslack,errorA):
    global sprocketArray
    setRatio(ratio)
    tratio=getFirstRatio(ratio)
    addAllSetsInCTCRange(tratio,CTC,dslack,errorA)
    #sprocketArray = sorted(sprocketArray, key= sprocketArray(terror))
    return

def findOptimalNearRatio(ratio,CTC,dslack,errorA):
    global sprocketArray
    setRatio(ratio)
    tratio=getFirstRatio(ratio)
    addAllSetsNearRatio(tratio,CTC,dslack,errorA)
    #sprocketArray = sorted(sprocketArray, key= sprocketArray(terror))    
    return sprocketArray

def getLeftOptimalRatio(left):
    global sprocketArray
    best=1
    for i in range (0,len(sprocketArray)):
        [bigTeeth,littleTeeth,big,little,fCTC,length,links,slack,terror,works,bd,ld]=sprocketArray[i]
        if littleTeeth==left:
            best=i
    retbest = sprocketArray[best]
    if len(sprocketArray)>1:
        #print(len(sprocketArray))
        #print(best)
        del sprocketArray[best]
    return retbest

def getRightOptimalRatio(right):
    global sprocketArray
    best=1
    for i in range (0,len(sprocketArray)):
        [bigTeeth,littleTeeth,big,little,fCTC,length,links,slack,terror,works,bd,ld]=sprocketArray[i]
        if bigTeeth==right:
            best=i  
    retbest = sprocketArray[best]
    if len(sprocketArray)>1:
        del sprocketArray[best]
    return retbest

def getOptimalRatio():
    global sprocketArray
    bslack=10000
    best=1
    for i in range (0,len(sprocketArray)):
        [bigTeeth,littleTeeth,big,little,fCTC,length,links,slack,terror,works,bd,ld]=sprocketArray[i]
        if bslack>slack and .5*getPitchDiameter(big)+.5*getPitchDiameter(little)<fCTC:
            best=i  
    #print(sprocketArray[best])
    retbest = sprocketArray[best]
    if len(sprocketArray)>1:
        del sprocketArray[best]
    return retbest

def addRatioSet(ratio,sArray):
    return newsArray

def getFirstRatio(tratio):
    print("getFirstRatio")
    global ratio
    (big,little)=ratio
    (bigTeeth,littleTeeth)=tratio
    tempBigTeeth=bigTeeth
    tempLittleTeeth=littleTeeth
    #check to make sure at least 6 teeth are touching
    for ratioScaler in range(1,28):
        if (.5*float(little*ratioScaler)<65) and (big*ratioScaler>=6):
            tempLittleTeeth = little * ratioScaler
            tempBigTeeth = big * ratioScaler
            #ValidRatiosaArray.append((tempLittle,tempBig,getSlack()))
            break
    bigTeeth=tempBigTeeth
    littleTeeth=tempLittleTeeth       
    tratio=(bigTeeth,littleTeeth)
    return tratio

def addAllSetsInCTCRange(tratio,fCTC,dslack,error):
    global ratio, sprocketArray
    (littleTeeth,bigTeeth)=tratio
    (lT,bT)=ratio
    big=bigTeeth
    little=littleTeeth
    global pitch
    #convert from mm to in
    #print(fCTC)
    CTC_M = fCTC
    CTC_C = fCTC/25.4
    #print(littleTeeth)
    CTCArray = getCenterToCenterList(getPitchDiameter(littleTeeth),getPitchDiameter(bigTeeth),pitch,.13/2,dslack,CTC_C)
    for x in range(0,10):
        #print(CTC)
        [CTC,length,links]= CTCArray[x]
        slack = links%1 
        terror = abs(slack-dslack)
        works = True
        #print(littleTeeth)
        if terror > error:
            works = False
        infoset = (littleTeeth,bigTeeth,little,big,CTC,length,links,slack,terror,works,getPitchDiameter(littleTeeth)+getAmplitude(littleTeeth),getPitchDiameter(bigTeeth)+getAmplitude(bigTeeth))
        #print(infoset)
        if littleTeeth >= 8 and bigTeeth >= 8:
            sprocketArray.append(infoset)
    return

def addAllSetsNearRatio(tratio,fCTC,dslack,error):
    print("addAllSetsNearRatio")
    global ratio, sprocketArray
    (bigTeeth,littleTeeth)=tratio
    (bT,lT)=ratio
    big=bigTeeth
    little=littleTeeth
    global pitch
    for  v in range(0,6):
        length = getLength(getPitchDiameter(littleTeeth),getPitchDiameter(bigTeeth),pitch,.13/2,fCTC)
        links=length/pitch
        slack = getSlack(getPitchDiameter(little),getPitchDiameter(bigTeeth),fCTC)
        terror=abs(slack-dslack)
        works=True
        if terror >error:
            works=False
        infoset = (bigTeeth,littleTeeth,big,little,fCTC,length,links,slack,terror,works,getPitchDiameter(bigTeeth)+getAmplitude(bigTeeth),getPitchDiameter(littleTeeth)+getAmplitude(littleTeeth))
        #print(infoset)
        if littleTeeth >= 8 and bigTeeth >= 8:
            sprocketArray.append(infoset)
        littleTeeth+=1
    for  v in range(0,6):
        big=bigTeeth
        little=littleTeeth
        length = getLength(getPitchDiameter(littleTeeth),getPitchDiameter(bigTeeth),pitch,.13/2,fCTC)
        links=length/pitch
        slack = getSlack(getPitchDiameter(little),getPitchDiameter(bigTeeth),fCTC)
        terror=abs(slack-dslack)
        works=True
        if terror >error:
            works=False
        infoset = (bigTeeth,littleTeeth,big,little,fCTC,length,links,slack,terror,works,getPitchDiameter(bigTeeth)+getAmplitude(bigTeeth),getPitchDiameter(littleTeeth)+getAmplitude(littleTeeth))
        #print(infoset)
        if littleTeeth >= 8 and bigTeeth >= 8:
            sprocketArray.append(infoset)
        littleTeeth-=1
    for  v in range(0,6):
        big=bigTeeth
        little=littleTeeth
        length = getLength(getPitchDiameter(littleTeeth),getPitchDiameter(bigTeeth),pitch,.13/2,fCTC)
        links=length/pitch
        slack = getSlack(getPitchDiameter(little),getPitchDiameter(bigTeeth),fCTC)
        terror=abs(slack-dslack)
        works=True
        if terror >error:
            works=False
        infoset = (bigTeeth,littleTeeth,big,little,fCTC,length,links,slack,terror,works,getPitchDiameter(bigTeeth)+getAmplitude(bigTeeth),getPitchDiameter(littleTeeth)+getAmplitude(littleTeeth))
        #print(infoset)
        if littleTeeth >= 8 and bigTeeth >= 8:
            sprocketArray.append(infoset)
        bigTeeth+=1
    for  v in range(0,6):
        big=bigTeeth
        little=littleTeeth
        length = getLength(getPitchDiameter(littleTeeth),getPitchDiameter(bigTeeth),pitch,.13/2,fCTC)
        links=length/pitch
        slack = getSlack(getPitchDiameter(little),getPitchDiameter(bigTeeth),fCTC)
        terror=abs(slack-dslack)
        works=True
        if terror >error:
            works=False
        infoset = (bigTeeth,littleTeeth,big,little,fCTC,length,links,slack,terror,works,getPitchDiameter(bigTeeth)+getAmplitude(bigTeeth),getPitchDiameter(littleTeeth)+getAmplitude(littleTeeth))
        #print(infoset)
        if littleTeeth >= 8 and bigTeeth >= 8:
            sprocketArray.append(infoset)
        bigTeeth-=1
    return

def addAllSetsInRatio(tratio,fCTC,dslack,error):
    print("addAllSetsInRatio")
    global ratio, sprocketArray
    (littleTeeth,bigTeeth)=tratio
    (lT,bT)=ratio
    big=bigTeeth
    little=littleTeeth
    global pitch
    while bigTeeth < 65:
        #print(bigTeeth)
        length = getLength(getPitchDiameter(littleTeeth),getPitchDiameter(bigTeeth),pitch,.13/2,fCTC)
        links=length/pitch
        slack = getSlack(getPitchDiameter(little),getPitchDiameter(bigTeeth),fCTC)
        terror=abs(slack-dslack)
        works=True
        if terror >error:
            works=False
        infoset = (bigTeeth,littleTeeth,big,little,fCTC,length,links,slack,terror,works,getPitchDiameter(bigTeeth)+getAmplitude(bigTeeth),getPitchDiameter(littleTeeth)+getAmplitude(littleTeeth))
        #print(infoset)
        if littleTeeth >= 8 and bigTeeth >= 8:
            sprocketArray.append(infoset)
        littleTeeth+=lT
        bigTeeth+=bT
    return
def findAllSetsInRatio(tratio,fCTC,dslack,error):
    print("findAllSetsInRatio")
    #fCTC = fCTC/25.4
    global ratio, sprocketArray
    (littleTeeth,bigTeeth)=tratio
    print(littleTeeth)
    (lT,bT)=ratio
    big=bigTeeth
    little=littleTeeth
    #print(CTC_C)
    global pitch
    while bigTeeth < 60:
        #print(bigTeeth)
        length = getLength(getPitchDiameter(littleTeeth),getPitchDiameter(bigTeeth),pitch,.13/2,fCTC)
        links=length/pitch
        #print(bigTeeth)
        slack = getSlack(getPitchDiameter(little),getPitchDiameter(bigTeeth),fCTC)
        terror=abs(slack-dslack)
        works=True
        if terror > error:
            works=False
        infoset = (bigTeeth,littleTeeth,big,little,fCTC,length,links,slack,terror,works,getPitchDiameter(bigTeeth)+getAmplitude(bigTeeth),getPitchDiameter(littleTeeth)+getAmplitude(littleTeeth))
        #print(infoset)
        sprocketArray.append(infoset)
        littleTeeth+=lT
        bigTeeth+=bT
    while littleTeeth > 12:
        #print(bigTeeth)
        length = getLength(getPitchDiameter(littleTeeth),getPitchDiameter(bigTeeth),pitch,.13/2,fCTC)
        links=length/pitch
        slack = getSlack(getPitchDiameter(little),getPitchDiameter(bigTeeth),fCTC)
        terror=abs(slack-dslack)
        works=True
        if terror >error:
            works=False
        infoset = (bigTeeth,littleTeeth,big,little,fCTC,length,links,slack,terror,works,getPitchDiameter(bigTeeth)+getAmplitude(bigTeeth),getPitchDiameter(littleTeeth)+getAmplitude(littleTeeth))
        #print(infoset)
        if littleTeeth >= 8 and bigTeeth >= 8:
            sprocketArray.append(infoset)
        littleTeeth-=lT
        bigTeeth-=bT
    return

def checkCenterToCenterCompatiblity(centerToCenter,centerToCenterList):
    #for i in range(0,50):
    return

def addVersaBearingHole(xorignin):
    if vexBearingHole:
        bpy.ops.mesh.primitive_cylinder_add(view_align=False, enter_editmode=False, location=(xorignin, 0, 0))
        bpy.ops.transform.resize(value=(0.568, 0.568, 0.568), proportional_size=1)
        bpy.context.scene.objects.active = bpy.data.objects[name]
        bpy.ops.object.modifier_add(type='BOOLEAN')
        bpy.context.object.modifiers["Boolean"].object = bpy.data.objects["Cylinder"]
        bpy.context.object.modifiers["Boolean"].operation = 'DIFFERENCE'
        bpy.ops.object.modifier_apply(apply_as='DATA', modifier="Boolean")
        bpy.ops.object.select_all(action='DESELECT')
        bpy.ops.object.select_pattern(pattern="Cylinder")
        bpy.ops.object.delete(use_global=False)
        return
    return

def addTetrixHub(xorignin):
    if tetrixHub:
        bpy.ops.mesh.primitive_cylinder_add(view_align=False, enter_editmode=False, location=(xorignin, 0, 0))
        bpy.ops.transform.resize(value=(0.325/2, 0.325/2, 0.15625), proportional_size=1)
        bpy.context.scene.objects.active = bpy.data.objects[name]
        bpy.ops.object.modifier_add(type='BOOLEAN')
        bpy.context.object.modifiers["Boolean"].object = bpy.data.objects["Cylinder"]
        bpy.context.object.modifiers["Boolean"].operation = 'DIFFERENCE'
        bpy.ops.object.modifier_apply(apply_as='DATA', modifier="Boolean")
        bpy.ops.object.select_all(action='TOGGLE')
        bpy.ops.object.select_pattern(pattern="Cylinder")
        bpy.ops.object.delete(use_global=False)
        for h in range(0,4):
            p=6.28/4*h
            x=0.31496*math.cos(p)
            y=0.31496*math.sin(p)
            bpy.ops.mesh.primitive_cylinder_add(view_align=False, enter_editmode=False, location=(x+xorignin, y, 0))
            bpy.ops.transform.resize(value=(0.145669/2, 0.145669/2, 0.145669), proportional_size=1)
            bpy.context.scene.objects.active = bpy.data.objects[name]
            bpy.ops.object.modifier_add(type='BOOLEAN')
            bpy.context.object.modifiers["Boolean"].object = bpy.data.objects["Cylinder"]
            bpy.context.object.modifiers["Boolean"].operation = 'DIFFERENCE'
            bpy.ops.object.modifier_apply(apply_as='DATA', modifier="Boolean")
            bpy.ops.object.select_all(action='TOGGLE')
            bpy.ops.object.select_pattern(pattern="Cylinder")
            bpy.ops.object.delete(use_global=False)
        return
    return

def addVersaHoles(xorignin):
    if versaHoles:
        for h in range(0,6):
            p=6.28/6*h
            x=0.9375*math.cos(p)
            y=0.9375*math.sin(p)
            bpy.ops.mesh.primitive_cylinder_add(view_align=False, enter_editmode=False, location=(x+xorignin, y, 0))
            bpy.ops.transform.resize(value=(0.0845, 0.0845, 0.563), proportional_size=1)
            bpy.context.scene.objects.active = bpy.data.objects[name]
            bpy.ops.object.modifier_add(type='BOOLEAN')
            bpy.context.object.modifiers["Boolean"].object = bpy.data.objects["Cylinder"]
            bpy.context.object.modifiers["Boolean"].operation = 'DIFFERENCE'
            bpy.ops.object.modifier_apply(apply_as='DATA', modifier="Boolean")
            bpy.ops.object.select_all(action='TOGGLE')
            bpy.ops.object.select_pattern(pattern="Cylinder")
            bpy.ops.object.delete(use_global=False)
        return
    return

def run(origo):
    origin = Vector(origo)
    resolutionScaler = 64
    resolution = 12*resolutionScaler
    p = 3.14/(6*resolutionScaler)
    amplitude=.13/2
    pitchDiameter=getPitchDiameter(teeth)
    rollerScaler=6.28/12
    myArrayVerts=[]
    myArrayFaces=[]
    myTopFace=[]
    myTopFaceWhole=[]
    myTopFaceHole=[]
    myBottomFace=[]
    z=0
    zModifier=0
    #z=teeth/2;
    #name= "PD%.3fT%d"%(pitchDiameter,teeth)

    for t in range(0, resolution):
        #myArrayVerts.append((t,t,t))
        x=pitchDiameter*.5*math.cos(t*p)+math.cos(t*p)*amplitude*math.cos(teeth*(t*p)+rollerScaler*math.sin(teeth*(t*p)))
        y=pitchDiameter*.5*math.sin(t*p)+math.sin(t*p)*amplitude*math.cos(teeth*(t*p)+rollerScaler*math.sin(teeth*(t*p)))
        #x=math.cos(t*p)
        #y=math.sin(t*p)
        xc=math.cos(t*p)
        yc=math.sin(t*p)
        #if (pitchDiameter/2+amplitude*math.cos(teeth*(t*p)+rollerScaler*math.sin(teeth*(t*p))))>(pitchDiameter/2):
          #  zModifier=(pitchDiameter/2-(pitchDiameter/2+amplitude*math.cos(teeth*(t*p)+rollerScaler*math.sin(teeth*(t*p)))))/2 
        myArrayVerts.append((x,y,z+thickness+zModifier-.01))
    
    for t in range(0, resolution):
        #myArrayVerts.append((pitchDiameter*math.cos(t*p)+math.cos(t*p)*amplitude*math.cos(teeth*(t*p)+rollerScaler*math.sin(teeth*(t*p))),pitchDiameter*math.sin(t*p)+math.sin(t*p)*amplitude*math.cos(teeth*(t*p)+rollerScaler*math.sin(teeth*(t*p))),0))
        x=pitchDiameter*.5*math.cos(t*p)+math.cos(t*p)*amplitude*math.cos(teeth*(t*p)+rollerScaler*math.sin(teeth*(t*p)))
        y=pitchDiameter*.5*math.sin(t*p)+math.sin(t*p)*amplitude*math.cos(teeth*(t*p)+rollerScaler*math.sin(teeth*(t*p)))
        #x=math.cos(t*p)
        #y=math.sin(t*p)
        xc=math.cos(t*p)
        yc=math.sin(t*p)
        #if (pitchDiameter/2+amplitude*math.cos(teeth*(t*p)+rollerScaler*math.sin(teeth*(t*p))))>(pitchDiameter/2):
        #    zModifier=(pitchDiameter/2-(pitchDiameter/2+amplitude*math.cos(teeth*(t*p)+rollerScaler*math.sin(teeth*(t*p)))))/2 
        myArrayVerts.append((x,y,z-zModifier))

    for t in range(0, resolution-1):
        myArrayFaces.append((t,t+1,t+resolution+1,t+resolution))
    #myArrayFaces.append((0,resolution,resolution+resolution,resolution+1))

    #for t in range(0, resolution):
        #myTopFace.append(t)
    
    myTopFace = range(0, resolution)
    
    for t in range(0, resolution):
            myBottomFace.append(t+resolution)
        
    #for t in range(0, 5):
     #   count=len(myArrayVerts)-1 -12 +t
      #  myArrayFaces.append((count, count+1,count+6+1,count+6))
    #myTopFaceWhole=(myTopFace/myTopFacesHole)
    
    myArrayFaces.append(myTopFace)
    myArrayFaces.append(myBottomFace)
    
    cone1 = createMeshFromData( name, origin, myArrayVerts, myArrayFaces)
    bpy.ops.object.editmode_toggle()
    bpy.ops.mesh.normals_make_consistent(inside=False)
    bpy.ops.object.editmode_toggle()
    return

def runTest(origo):
    origin = Vector(origo)
    myArrayVerts=[]
    myArrayFaces=[]
    myArrayBottomFace=[]
    myArrayTopFace=[]
    name= "hole"
    z=0
    
    for t in range(1, 7):
        p=6.28/6*t
        x=.2*math.cos(p)
        y=.2*math.sin(p)
        myArrayVerts.append((x,y,z+.15))
        myArrayBottomFace.append(len(myArrayVerts)-1)
        
    for t in range(1, 7):
        p=6.28/6*t
        x=.2*math.cos(p)
        y=.2*math.sin(p)
        myArrayVerts.append((x,y,z))
        myArrayTopFace.append(len(myArrayVerts)-1)
    
    for t in range(0, 5):
        count=t
        myArrayFaces.append((count, count+1,count+6+1,count+6))
    myArrayFaces.append((6, 0,7,12))
    myArrayFaces.append(myArrayTopFace)
    myArrayFaces.append(myArrayBottomFace)
    cone3 = createMeshFromData( name, origin, myArrayVerts, myArrayFaces)
    return

def drawAndExportSTL(teethA,teethB,fname):
    # set globals to prep methods
    setTeeth(teethA)
    setName(teethA)

    # makes the first sprocket sprocket with holes
    print(teethA)
    run((0,0,0))
    addVersaBearingHole(0)
    addVersaHoles(0)
    addTetrixHub(0)
    bpy.ops.object.select_all(action='SELECT')
    exportSTL(fname,"(A)")
    
    # clear the "room"
    bpy.ops.object.select_all(action='SELECT')
    bpy.ops.object.delete(use_global=False)
    
    # set globals to prep methods
    setTeeth(teethB)
    setName(teethB)
    
    # makes the first sprocket sprocket with holes
    run((0,0,0))
    addVersaBearingHole(0)
    addVersaHoles(0)
    addTetrixHub(0)
    bpy.ops.object.select_all(action='SELECT')
    exportSTL(fname,"(B)")
    
    return

def drawAndExportPicture(teethA,teethB,fname):
    
    # makes the first sprocket sprocket with holes
    bpy.ops.object.select_all(action='SELECT')
    setTeeth(teethA)
    setName(teethA)
    run((0,0,0))
    addVersaBearingHole(0)
    addVersaHoles(0)
    addTetrixHub(0)

    # draw second sprocket
    setTeeth(teethB)
    setName(teethB)    
    x=.5+ .5*(getPitchDiameter(teethA)+getPitchDiameter(teethB))
    #(xModifier,l,li)=x[0]
    #print(xModifier)
    run((x,0,0))
    addVersaBearingHole(x)
    addVersaHoles(x)
    addTetrixHub(x)
    exportImage(fname)
    return

def exportSTL(fname,tag):
    #argv = sys.argv
    #argv = argv[argv.index("--")+ 1:]
    #filename = str(argv[0])+tag
    bpy.ops.transform.resize(value=(25.4, 25.4, 25.4), proportional_size=25.4)
    filename = fname+tag+".stl"
    print(filename)
    basedir = os.path.dirname(bpy.data.filepath)
    filePath ="/var/www-chapresearch/SprocketR_Output"
    #bpy.context.scene.objects.active = bpy.data.objects[name]
    print(name)
    bpy.ops.object.select_all(action='SELECT')
    fn = os.path.join(filePath, filename)
    bpy.ops.export_mesh.stl(check_existing=True, filepath=fn, filter_glob=".stl", axis_forward='X', axis_up='Y', global_scale=1, ascii=True, use_mesh_modifiers=True)
    print("Saved:", fn)
    return

def exportImage(fname):
    scene = bpy.context.scene
    #argv = sys.argv
    #argv = argv[argv.index("--")+ 1:]
    filename = fname + ".png"
    basedir = os.path.dirname(bpy.data.filepath)
    filePath ="/var/www-chapresearch/SprocketR_Output"
    bpy.context.scene.objects.active = bpy.data.objects[name]
    lamp_data = bpy.data.lamps.new(name="Lamp", type='POINT')
    lamp_object = bpy.data.objects.new(name="New Lamp", object_data=lamp_data)
    scene.objects.link(lamp_object)
    # Place lamp to a specified location
    lamp_object.location = (5.0, 5.0, 5.0)
    # And finally select it make active
    lamp_object.select = True
    scene.objects.active = lamp_object
    #bpy.context.scene.objects.active = bpy.ops.object.select_by_type('MESH')
    bpy.ops.object.select_all(action='SELECT')
    fn = os.path.join(filePath, fname)
    #bpy.ops.object.camera_add(view_align=True, enter_editmode=False, location=(.5*xModifier, 0, 6), rotation=(0, 0, 0), layers=(True, False, False, False, False, False, False, False, False, False, False, False, False, False, False, False, False, False, False, False))
    cam = bpy.data.objects['Camera']
    #cam = bpy.ops.object.select_by_type('CAMERA')
    bpy.data.scenes['Scene'].render.filepath = fn
    bpy.ops.render.render( write_still=True )
    return
    
def processArray(uarg):
    (name, uratio,uCTC,chain,left,right,uerror,uperror,refinetype)= uarg
    global ratio, CTC, sprocketArray
    processChainNum(chain)
    if refinetype=='ratio':
        print("run ratio")
        addAllSetsNearRatio(uratio,uCTC,uerror,uperror)
        best = getOptimalRatio()
        (littleTeeth,bigTeeth,little,big,fCTC,length,links,slack,terror,works,littleDia,bigDia)=best
        options=[]
        i = len(sprocketArray)
        for x in range(0,i):                
            #reduce fraction
            check=Fraction(little, big)
            little=check.numerator
            big=check.denominator
            length_c = length/25.4
            #des_slack = ((((uerror/100.0)+1.0) * length_c)-length_c)*4
            des_slack = ((((uerror/100.0)) * length_c))
            '''
            if des_slack<slack:
            over_under = -1*(slack - des_slack)
            else:
            over_under = des_slack - slack
            '''
            length_full = (length_c + des_slack) + pitch - ((length_c + des_slack) % pitch)
            over_under = ((length_full - length_c - des_slack))/pitch
            slack = (length_full - length_c)/pitch
            sprocket={}
            sprocket["bigTeeth"]=bigTeeth
            sprocket["littleTeeth"]=littleTeeth
            sprocket["ratio_big"]=big
            sprocket["ratio_little"]=little
            sprocket["Center To Center"]=round(fCTC,3)
            sprocket["length"]=round(length_full/pitch,3)
            sprocket["links"]=links
            sprocket["slack"]=round(slack,3)
            sprocket["error"]=terror
            sprocket["err_acceptable"]=works
            sprocket["bigDiameter"]=round(bigDia/0.0393701,1)
            sprocket["littleDiameter"]=round(littleDia/0.0393701,1)
            sprocket["distFromDesiredSlack"]=round(over_under,3)
            sprocket["desiredSlack"]=uerror
            #sprocket["total_length"]= length+
            options.append(sprocket)
            best = getOptimalRatio()
            (littleTeeth,bigTeeth,little,big,fCTC,length,links,slack,terror,works,littleDia,bigDia)=best
        #one["one"]=best
        best= getOptimalRatio()
        if uerror != 0:
            #options = sorted(options, key=lambda sprocket: sprocket["distFromDesiredSlack"])
            options.sort(key=operator.itemgetter("distFromDesiredSlack"))
        print("JSON output")
        print(json.dumps(options))
        print("Finished")
        return best

    if refinetype=='c2c':
        print("run CTC")
        addAllSetsInCTCRange(uratio,uCTC,uerror,uperror)
        best = getOptimalRatio()
        (littleTeeth,bigTeeth,little,big,fCTC,length,links,slack,terror,works,littleDia,bigDia)=best
        options=[]
        i = len(sprocketArray)
        for x in range(0,i):                
            #reduce fraction
            check=Fraction(little, big)
            little=check.numerator
            big=check.denominator
            hypotenuseLength = math.sqrt(((.5*bigDia - .5*littleDia)**2 + (fCTC)**2))
            length_actual = 2*hypotenuseLength + (getArcLengthMinor(littleDia, bigDia, fCTC) * littleDia * 3.14159) + ((1-getArcLengthMinor(littleDia, bigDia, fCTC)) * bigDia * 3.14159)
            sprocket={}
            sprocket["bigTeeth"]=bigTeeth
            sprocket["littleTeeth"]=littleTeeth
            sprocket["ratio_big"]=big
            sprocket["ratio_little"]=little
            sprocket["Center To Center"]=round(fCTC*25.4,3)
            sprocket["length"]=round(length/pitch,3)
            sprocket["links"]=links
            sprocket["slack"]=round((length - length_actual)/pitch,3)
            sprocket["error"]=terror
            sprocket["err_acceptable"]=works
            sprocket["bigDiameter"]=round(bigDia/0.0393701,1)
            sprocket["littleDiameter"]=round(littleDia/0.0393701,1)
            sprocket["distFromDesiredSlack"]=0
            sprocket["desiredSlack"]=uerror
            #sprocket["total_length"]= length+
            options.append(sprocket)
            best = getOptimalRatio()
            (littleTeeth,bigTeeth,little,big,fCTC,length,links,slack,terror,works,littleDia,bigDia)=best
        #one["one"]=best
        best= getOptimalRatio()
        #if uerror != 0:
            #options = sorted(options, key=lambda sprocket: sprocket["distFromDesiredSlack"])
            #options.sort(key=operator.itemgetter("distFromDesiredSlack"))
        print("JSON output")
        print(json.dumps(options))
        print("Finished")
        return best
  
    else:
        if left == 0:
            if right == 0:
                print("run norm")
                findOptimalRatio(uratio,uCTC,uerror,uperror)
                best = getOptimalRatio()
                (bigTeeth,littleTeeth,big,little,fCTC,length,links,slack,terror,works,bigDia,littleDia)=best
                options=[]
                i = len(sprocketArray)
                for x in range(0,i):
                    #reduce fraction
                    check=Fraction(little, big)
                    little=check.numerator
                    big=check.denominator
                    length_c = length/25.4
                    #des_slack = ((((uerror/100.0)+1.0) * length_c)-length_c)*4
                    des_slack = ((((uerror/100.0)) * length_c))
                    '''
                    if des_slack<slack:
                        over_under = -1*(slack - des_slack)
                    else:
                        over_under = des_slack - slack
                    '''
                    length_full = (length_c + des_slack) + pitch - ((length_c + des_slack) % pitch)
                    over_under = ((length_full - length_c - des_slack))/pitch
                    slack = (length_full - length_c)/pitch
                    sprocket={}
                    sprocket["bigTeeth"]=bigTeeth
                    sprocket["littleTeeth"]=littleTeeth
                    sprocket["ratio_big"]=big
                    sprocket["ratio_little"]=little
                    sprocket["Center To Center"]=round(fCTC,3)
                    sprocket["length"]=round(length_full/pitch,3)
                    sprocket["links"]=links
                    sprocket["slack"]=round(slack,3)
                    sprocket["error"]=terror
                    sprocket["err_acceptable"]=works
                    sprocket["bigDiameter"]=round(bigDia/0.0393701,1)
                    sprocket["littleDiameter"]=round(littleDia/0.0393701,1)
                    sprocket["distFromDesiredSlack"]=round(over_under,3)
                    sprocket["desiredSlack"]=uerror
                    #sprocket["total_length"]= length+
                    options.append(sprocket)
                    best = getOptimalRatio()
                    #print(getArcLengthMinor(bigDia, littleDia, fCTC/25.4))
                    (bigTeeth,littleTeeth,big,little,fCTC,length,links,slack,terror,works,bigDia,littleDia)=best
                #one["one"]=best
                best= getOptimalRatio()
                if uerror != 0:
                    #options = sorted(options, key=lambda sprocket: sprocket["distFromDesiredSlack"],sprocket["distFromDesiredSlack"])
                    options.sort(key=operator.itemgetter("distFromDesiredSlack"))
                #for i in options:
                    #print(i['distFromDesiredSlack'])
                print("JSON output")
                print(json.dumps(options))
                print("Finished")
            
                return best
            else:
                print("right")
                findSideRatio(uratio,right,uCTC,uerror,uperror)
                best = getRightOptimalRatio(right)
                (bigTeeth,littleTeeth,big,little,fCTC,length,links,slack,terror,works,bigDia,littleDia)=best
                options=[]
                y=5
                if len(sprocketArray)<5:
                    y=len(sprocketArray)
                
                for x in range(0,5):
                    #reduce fraction
                    #reduce fraction
                    check=Fraction(little, big)
                    little=check.numerator
                    big=check.denominator
                    length_c = length/25.4
                    #des_slack = ((((uerror/100.0)+1.0) * length_c)-length_c)*4
                    des_slack = ((((uerror/100.0)) * length_c))
                    '''
                    if des_slack<slack:
                        over_under = -1*(slack - des_slack)
                    else:
                        over_under = des_slack - slack
                    '''
                    length_full = (length_c + des_slack) + pitch - ((length_c + des_slack) % pitch)
                    over_under = ((length_full - length_c - des_slack))/pitch
                    slack = (length_full - length_c)/pitch
                    sprocket={}
                    sprocket["bigTeeth"]=bigTeeth
                    sprocket["littleTeeth"]=littleTeeth
                    sprocket["ratio_big"]=big
                    sprocket["ratio_little"]=little
                    sprocket["Center To Center"]=round(fCTC,3)
                    sprocket["length"]=round(length_full/pitch,3)
                    sprocket["links"]=links
                    sprocket["slack"]=round(slack,3)
                    sprocket["error"]=terror
                    sprocket["err_acceptable"]=works
                    sprocket["bigDiameter"]=round(bigDia/0.0393701,1)
                    sprocket["littleDiameter"]=round(littleDia/0.0393701,1)
                    sprocket["distFromDesiredSlack"]=round(over_under,3)
                    sprocket["desiredSlack"]=uerror
                    #sprocket["total_length"]= length+
                    options.append(sprocket)
                    best = getOptimalRatio()
                    #best = getOptimalRatio()
                    (bigTeeth,littleTeeth,big,little,fCTC,length,links,slack,terror,works,bigDia,littleDia)=best
                #one["one"]=best
                best= getOptimalRatio()
                if uerror != 0:
                    options = sorted(options, key=lambda sprocket: sprocket["distFromDesiredSlack"])
                print("JSON output")
                print(json.dumps(options))
                print("Finished")
                return best
        elif right ==  0:
            print("left")
            #print(uCTC)
            findSideRatio(uratio,left,uCTC,uerror,uperror)
            print(uCTC)
            best = getLeftOptimalRatio(left)
            (bigTeeth,littleTeeth,big,little,fCTC,length,links,slack,terror,works,bigDia,littleDia)=best
            options=[]
            y=5
            if len(sprocketArray)<5:
                y=len(sprocketArray)
                
            for x in range(0,5):
                #reduce fraction
                check=Fraction(little, big)
                little=check.numerator
                big=check.denominator
                length_c = length/25.4
                #des_slack = ((((uerror/100.0)+1.0) * length_c)-length_c)*4
                des_slack = ((((uerror/100.0)) * length_c))
                length_full = (length_c + des_slack) + pitch - ((length_c + des_slack) % pitch)
                over_under = ((length_full - length_c - des_slack))/pitch
                slack = (length_full - length_c)/pitch
                sprocket={}
                sprocket["bigTeeth"]=bigTeeth
                sprocket["littleTeeth"]=littleTeeth
                sprocket["ratio_big"]=big
                sprocket["ratio_little"]=little
                sprocket["Center To Center"]=round(fCTC,3)
                sprocket["length"]=round(length_full/pitch,3)
                sprocket["links"]=links
                sprocket["slack"]=round(slack,3)
                sprocket["error"]=terror
                sprocket["err_acceptable"]=works
                sprocket["bigDiameter"]=round(bigDia/0.0393701,1)
                sprocket["littleDiameter"]=round(littleDia/0.0393701,1)
                sprocket["distFromDesiredSlack"]=round(over_under,3)
                sprocket["desiredSlack"]=uerror
                #sprocket["total_length"]= length+
                options.append(sprocket)
                best = getOptimalRatio()
                (bigTeeth,littleTeeth,big,little,fCTC,length,links,slack,terror,works,bigDia,littleDia)=best
            #one["one"]=best
            best= getOptimalRatio()
            print("JSON output")
            print(json.dumps(options))
            print("Finished")
            #best = getLeftOptimalRatio(left)
            return best
        else:
            print("both")
            (lT,bT)=uratio
            big=bT
            little=lT
            length = getLength(getPitchDiameter(left),getPitchDiameter(right),pitch,.13/2,uCTC)
            links=length/pitch
            slack = getSlack(getPitchDiameter(little),getPitchDiameter(big),uCTC)
            terror=abs(slack-uerror)
            works=True
            if terror >uperror:
                works=False
            infoset = (left,right,big,little,uCTC,length,links,slack,terror,works,getPitchDiameter(left)+getAmplitude(left),getPitchDiameter(right)+getAmplitude(right))
            #sprocketArray.append(infoset)
            (littleTeeth,bigTeeth,little,big,fCTC,length,links,slack,terror,works,littleDia,bigDia)=infoset
            
            options=[]            
            #reduce fraction
            check=Fraction(little, big)
            little=check.numerator
            big=check.denominator
            length_c = length/25.4
            des_slack = ((((uerror/100.0)) * length_c))
            length_full = (length_c + des_slack) + pitch - ((length_c + des_slack) % pitch)
            over_under = ((length_full - length_c - des_slack))/pitch
            slack = (length_full - length_c)/pitch
            
            sprocket={}
            sprocket["bigTeeth"]=bigTeeth
            sprocket["littleTeeth"]=littleTeeth
            sprocket["ratio_big"]=little
            sprocket["ratio_little"]=big
            sprocket["Center To Center"]=round(fCTC,3)
            sprocket["length"]=round(length_full/pitch,3)
            sprocket["links"]=links
            sprocket["slack"]=round(slack,3)
            sprocket["error"]=terror
            sprocket["err_acceptable"]=works
            sprocket["bigDiameter"]=round(bigDia/0.0393701,1)
            sprocket["littleDiameter"]=round(littleDia/0.0393701,1)
            sprocket["distFromDesiredSlack"]=round(over_under,3)
            sprocket["desiredSlack"]=uperror
            options.append(sprocket)
            #best = getOptimalRatio()
            #(bigTeeth,littleTeeth,big,little,fCTC,length,links,slack,terror,works,bigDia,littleDia)=best
            #one["one"]=best
            #best= getOptimalRatio()
            #two={}
            #two["two"] = best
            print("JSON output")
            print(json.dumps(options))
            print("Finished")
            #best = getLeftOptimalRatio(left)
            return 
        return
 
if __name__ == "__main__":
    params=sys.argv
    
    #if len(params)!=0:
    #params = params[params.index("--") 1+ 1:]
    params = params[(params.index("--")+1):]
    print(params)
    rat = params[1].split(":")
    params[1]=(int(rat[0]),int(rat[1]))
    params[2]=float(params[2])
    params[4]=int(params[4])
    params[5]=int(params[5])
    params[6]=float(params[6])
    params[7]=float(params[7])
    switchtwo = params[11]
    versahub = params[8]
    tetrixhub = params[9]
    versahole= params[10]
    print(params)
    del params[10]
    del params[9]
    del params[8]
    print("out",params)
    #(teethA,teethB,littleTeeth,bigTeeth,fCTC,length,links,slack,terror,works,bd,ld)=processArray(params)
    #print("little",teethA)
    #print("big",teethB)
    
    #teethA=23
    #teethA=int(str(params[4]))
    #teethB=26
    ''' sets the first sprockets values '''
    '''
    if(versahub=='on'):
        #setVexBearingHole(True)
        setVersaHoles(True)
    if(tetrixhub=='on'):
        setTetrixHub(True)
    if(versahole=='on'):
        setVexBearingHole(True)
    '''
    (littleTeeth,bigTeeth) = params[1]
    whole = params[0].split(".")
    (fname,switch)=whole
    if switch=="stl":
        if(versahub=='on'):
            #setVexBearingHole(True)
            setVersaHoles(True)
        if(tetrixhub=='on'):
            setTetrixHub(True)
        if(versahole=='on'):
            setVexBearingHole(True)
        bpy.ops.object.select_all(action='SELECT')
        bpy.ops.object.delete(use_global=False)
        drawAndExportSTL(littleTeeth,bigTeeth,fname)
    elif switch=="data":
        processArray(params)
    else:
        if(versahub=='on'):
            #setVexBearingHole(True)
            setVersaHoles(True)
        if(tetrixhub=='on'):
            setTetrixHub(True)
        if(versahole=='on'):
            setVexBearingHole(True)
        drawAndExportPicture(littleTeeth,bigTeeth,fname)
    
    
    #bpy.ops.object.select_all(action='TOGGLE')
    #runTest((0,0,0))
    '''
    (CTC,length,links)=x[3]
    optimal =findOptimalRatio(ratio,CTC,dslack,errorA)
    tobj=sprocketArray[getOptimalRatio()]
    '''
    '''
    bpy.ops.object.select_all(action='TOGGLE')
    bpy.context.scene.objects.active = bpy.data.objects[name]
    bpy.ops.transform.resize(value=(1/39.3701, 1/39.3701, 1/39.3701))
    #bpy.ops.transform.translate(value=(x, 0, 0), constraint_axis=(False, False, False), constraint_orientation='GLOBAL', mirror=False, proportional='DISABLED', proportional_edit_falloff='SMOOTH', proportional_size=1, release_confirm=True)
    '''
    
    '''
    x=getCenterToCenterList(getPitchDiameter(teethA),getPitchDiameter(teethB),.25,.1333)
    for i in range(0,10):
        (CTC,length,links)=x[i]
        print(" CTC %0.3f - length %0.3f - links %0.3f"%(CTC,length,links))
    #print(optimal)
    print(tobj)
    '''
