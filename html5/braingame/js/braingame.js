

var lastAnim = 0;
var score = 0;
var round = 0;
var questions = [];
var brainAnimInterval;
var brainAnim;
var answers = "";
var sceneObj;
var cur_brain_width;
var cur_brain_height;
var game_physics;
var timerLabel;
const scoreLabelFillColor = '#ffb600';
const scoreLabelStrokeColor = '#000000';
const timerLabelBlinkingFillColor = '#ff0000';

var animConfig = {
    type: Phaser.CANVAS,
    parent: 'brainCanvas',
    width: 800,
    height: 600,
    physics: {
        default: 'arcade',
        arcade: {
            debug: false
        }
    },
    scene: {
        preload: preload,
        create: create,
        update: update,
        countdown: false
    }
};

function gameInit() {
    var brainCanvas = new Phaser.Game(animConfig);
    brainCanvas.scene.disableVisibilityChange = true;

    $('#html-container').append('<div id="startGame" style="background-image:url(html5/braingame/assets/brain/playButton.png);"> </div>');
    $('#startGame').on('click', function () {
        startingScreen.destroy();
        $('#startGame').remove();
        $('#question-field').css('display', '');
        $(sceneObj).trigger('startGame');
        questionUpdate();
        timerLabel.setVisible(true); // show the timer
        sceneObj.countdown.start(manageCountdownEnded.bind(this), BRAINGAME_TIMER_DURATION);
    });
}

// WebFontConfig = {
// 	active: function () {
// 	},
// 	google: {
// 		families: ['Revalia', 'Luckiest Guy']
// 	}
// };

function preload() {
    
    // console.log('Assets loading');

    var loader = this.load;

    // this.load.script('webfont', '//ajax.googleapis.com/ajax/libs/webfont/1.4.7/webfont.js');
    this.load.script('webfont', '/mod/exagames/js/webfont.1.4.7.js', function () {
        WebFont.load({
            custom: {
                families: ['Luckiest Guy'], // 'Revalia',
                // urls: ['/mod/exagames/html5/braingame/braingame-fonts.css'] // configured in braingame.css
            }
        });
    });

    // if the user has "disabled cache" browser option - sometimes the game is not working correctly
    // so, use preloaded HTML images (look also view.php):
    const images = {
        baseBackground: 'baseBackground.png',
        starwayBase: 'stairway_basic.png',
        cloudBase: 'cloud_base.png',
        brainTable: 'brain_table_basic.png',
        brain: 'brain.png',
        einstein_thumbs: 'einstein_thumbs-up.png',
        lamp: 'lamp.png',
        clipboard: 'clipboard.png',
        crow: 'crow.png',
        sky: 'sky3.png',
        startingScreen: 'startScreen.png',
        water: 'water.png',
        brain_indicator: 'brain_indicator.png'
    };
    for (const [key, imageName] of Object.entries(images)) {
        const imgElement = document.getElementById('preloaded_' + imageName);
        if (imgElement) {
            // Add the image to the texture manager if it exists in HTML
            this.textures.addImage(key, imgElement);
        } else {
            // Otherwise, load the image with the key
            loader.image(key, 'html5/braingame/assets/brain/' + imageName);
        }
    }

    /*        loader.image('baseBackground', 'html5/braingame/assets/brain/baseBackground.png');
            loader.image('starwayBase', 'html5/braingame/assets/brain/stairway_basic.png');
            loader.image('cloudBase', 'html5/braingame/assets/brain/cloud_base.png');
            loader.image('brainTable', 'html5/braingame/assets/brain/brain_table_basic.png');
            loader.image('brain', 'html5/braingame/assets/brain/brain.png');
            loader.image('einstein_thumbs', 'html5/braingame/assets/brain/einstein_thumbs-up.png');
            loader.image('lamp', 'html5/braingame/assets/brain/lamp.png');
            loader.image('clipboard', 'html5/braingame/assets/brain/clipboard.png');
            loader.image('crow', 'html5/braingame/assets/brain/crow.png');
            loader.image('sky', 'html5/braingame/assets/brain/sky3.png');
            loader.image('startingScreen', 'html5/braingame/assets/brain/startScreen.png');
            loader.image('water', 'html5/braingame/assets/brain/water.png');
            loader.image('brain_indicator', 'html5/braingame/assets/brain/brain_indicator.png');*/

    // The same for spritesheets, but here we also need to have sizes:
    var ssElement = document.getElementById('preloaded_einstein-flying.png');
    if (ssElement) {
        this.textures.addSpriteSheet('einstein_flying', ssElement, {
            frameWidth: 154,
            frameHeight: 282
        });
    } else {
        loader.spritesheet('einstein_flying', 'html5/braingame/assets/brain/einstein-flying.png', {
            frameWidth: 154,
            frameHeight: 282
        });
    }
    ssElement = document.getElementById('preloaded_einstein_smirk.png');
    if (ssElement) {
        this.textures.addSpriteSheet('einstein_smirk', ssElement, {
            frameWidth: 144,
            frameHeight: 273
        });
    } else {
        loader.spritesheet('einstein_smirk', 'html5/braingame/assets/brain/einstein_smirk.png', {
            frameWidth: 144,
            frameHeight: 273
        });
    }
    ssElement = document.getElementById('preloaded_einstein_mad.png');
    if (ssElement) {
        this.textures.addSpriteSheet('einstein_mad', ssElement, {
            frameWidth: 144,
            frameHeight: 273
        });
    } else {
        loader.spritesheet('einstein_mad', 'html5/braingame/assets/brain/einstein_mad.png', {
            frameWidth: 144,
            frameHeight: 273
        });
    }
    ssElement = document.getElementById('preloaded_stairway_destroyed.png');
    if (ssElement) {
        this.textures.addSpriteSheet('starway_destroyed', ssElement, {
            frameWidth: 301,
            frameHeight: 171
        });
    } else {
        loader.spritesheet('starway_destroyed', 'html5/braingame/assets/brain/stairway_destroyed.png', {
            frameWidth: 301,
            frameHeight: 171
        });
    }
    ssElement = document.getElementById('preloaded_einstein_splash.png');
    if (ssElement) {
        this.textures.addSpriteSheet('einstein_sinking', ssElement, {
            frameWidth: 152.5,
            frameHeight: 283
        });
    } else {
        loader.spritesheet('einstein_sinking', 'html5/braingame/assets/brain/einstein_splash.png', {
            frameWidth: 152.5,
            frameHeight: 283
        });
    }

}

var isTableFalling = false;
var isSkyCameraRoll = false;
var einstein_sky_floating = false;
var isEinsteinSinking = false;
var isBrainLocatorVisible = false;
var brain_location_shrinking = 1;
var brain_shrinking = true;
var cur_brain_inc_width = 32;
var cur_brain_inc_height = 32;


function update() {

    if (isTableFalling) {
        brainTable.angle -= 3;
        brainTable.x -= 1.5;
    }

    if (isSkyCameraRoll) {
        this.cameras.main.scrollY -= 10.5;
        einstein_flying.y -= 10.5;

    }

    if (einstein_sky_floating) {
        einstein_flying.angle -= 0.01;
    }

    if (isEinsteinSinking) {
        einstein_sinking.y += 7;
    }

    if (isBrainLocatorVisible) {
        if (brain_location_shrinking) {
            brain_location.displayWidth--;
            brain_location.displayHeight--;
        } else {
            brain_location.displayWidth++;
            brain_location.displayHeight++;
        }
        if (brain_location.displayWidth > 160) {
            brain_location_shrinking = true;
        }

        if (brain_location.displayWidth < 130) {
            brain_location_shrinking = false;
        }

    }


    if (brain_shrinking) {
        brain.displayWidth = (brain.displayWidth + 0.1);
        brain.displayHeight = (brain.displayHeight + 0.1);
    } else {
        brain.displayWidth = (brain.displayWidth - 0.1);
        brain.displayHeight = (brain.displayHeight - 0.1);
    }

    if (brain.displayWidth > cur_brain_width * 1.1) {
        brain_shrinking = false;
    }

    if (brain.displayWidth < cur_brain_width * 0.9) {
        brain_shrinking = true;
    }

    sceneObj.countdown.update();

}

/*function brainAnimation(brain) {
      let incWidth = (brain.displayWidth*0.1)/10;
    let incHeight = (brain.displayHeight*0.1)/10;
    brainAnimInterval = setInterval(function()  {
         transCnt = 0;
         brainAnim = setInterval(function() {
             transCnt++;
            brain.displayWidth = transCnt <= 10 ? brain.displayWidth + incWidth:brain.displayWidth - incWidth;
             brain.displayHeight = transCnt <= 10 ? brain.displayHeight + incHeight:brain.displayHeight - incHeight;
             if(transCnt >= 20) {
                 clearInterval(brainAnim);
             }

         }, 50);
    }, 1300);
  }*/


var baseCloud;
var growCount = 1;

class Animator {

    constructor() {
        this.animations = [];
        this.singles = [];
    }

    start = (cb, duration, delay, postProcessorFunc, postProcessorDelay) => {
        let startTime = Date.now() + delay;
        this.animations.push({
            func: cb,
            from: startTime,
            duration: duration,
            to: startTime + duration,
            postProcessorFunc: postProcessorFunc,
            liveTo: startTime + duration + postProcessorDelay
        });

    };


    once = (cb, delay, repeat) => {
        this.singles.push({
            func: cb,
            startAfter: Date.now() + delay,
            repeat: repeat,
            delay: delay
        })
    };


    update = () => {
        if (this.animations.length > 0) {
            for (let i = 0; i < this.animations.length; i++) {
                let a = this.animations[i];
                let now = Date.now();
                if (a.from < now && now < a.to) {
                    let prog = (now - a.from) / a.duration;
                    a.func(prog);
                } else if (now >= a.liveTo) {
                    if (a.postProcessorFunc != null) {
                        a.postProcessorFunc();
                    }
                    this.animations.splice(i, 1);

                }
            }
        }

        if (this.singles.length > 0) {
            for (let i = 0; i < this.singles.length; i++) {
                let s = this.singles[i];
                let now = Date.now();
                if (s.startAfter < now) {
                    s.func();
                    this.singles.splice(i, 1);

                    if (s.repeat) {

                        this.singles.push({
                            func: s.func,
                            startAfter: now + s.delay,
                            repeat: s.repeat,
                            delay: s.delay
                        })
                    }
                }
            }
        }

        requestAnimationFrame(this.update);

    };
}


function create() {

    var animator = new Animator();

    sceneObj = this;

    game_physics = this.physics;
    this.add.image(401, 300, 'baseBackground');
    einstein_sinking = sceneObj.physics.add.sprite(300, -300, 'einstein_sinking');
    einstein_sinking.visible = false;
    this.add.image(401, 520, 'water');

    crow = this.physics.add.sprite(1000, 100, 'crow');
    starway_destroyed = this.physics.add.sprite(630, 520, 'stairway_destroyed');
    starway_destroyed.visible = false;
    starway = this.add.image(630, 520, 'starwayBase');
    brainTable = this.physics.add.sprite(560, 400, 'brainTable');
    brain = this.physics.add.sprite(560, 310, 'brain');
    baseCloud = this.physics.add.sprite(800, 50, 'cloudBase');

    einstein = this.physics.add.sprite(680, 350, 'einstein_smirk');
    clipboard = this.add.image(240, 315, 'clipboard');

    let baseCloud_xValue = baseCloud.x;
    crow.body.immovable = true;
    baseCloud.body.velocity.set(-130, 0);
    /*setInterval(function(){
        baseCloud.x = baseCloud_xValue+100;
    }, 9000);
*/
    animator.once(function () {
        baseCloud.x = baseCloud_xValue + 100;
    }, 9000, true);


    //einstein_flying.visible = false;

    this.anims.create({
        key: 'smirk',
        frames: this.anims.generateFrameNumbers('einstein_smirk', {start: 0, end: 5}),
        frameRate: 40,
    });

    this.anims.create({
        key: 'thumbs_up',
        frames: this.anims.generateFrameNumbers('einstein_smirk', {start: 6, end: 6}),
        frameRate: 20
    });

    this.anims.create({
        key: 'mad',
        frames: this.anims.generateFrameNumbers('einstein_mad', {start: 0, end: 2}),
        frameRate: 8,
        repeat: 2
    });

    this.anims.create({
        key: 'starway_destroyed',
        frames: this.anims.generateFrameNumbers('starway_destroyed', {start: 0, end: 4}),
        frameRate: 70
    });

    this.anims.create({
        key: 'einstein_flying',
        frames: this.anims.generateFrameNumbers('einstein_flying', {start: 0, end: 1}),
        frameRate: 6,
        repeat: 9999
    });

    this.anims.create({
        key: 'einstein-in-sky',
        frames: this.anims.generateFrameNumbers('einstein_flying', {start: 2, end: 2}),
        frameRate: 20
    });

    this.anims.create({
        key: 'einstein_sinking',
        frames: this.anims.generateFrameNumbers('einstein_sinking', {start: 1, end: 3}),
        frameRate: 20
    });


    brain.displayWidth *= 0.3;
    brain.displayHeight *= 0.3;
    cur_brain_width = brain.displayWidth;
    cur_brain_height = brain.displayHeight;

    /*var smirkInterval = setInterval(function() {
         einstein.anims.play('smirk', false);
         setTimeout(function() {
             einstein.anims.play('smirk', false);
         }, 400);

     }, 5000);*/

    animator.once(function () {
        einstein.anims.play('smirk', false);
    }, 5000, true);

    animator.once(function () {
        einstein.anims.play('smirk', false);
    }, 5150, true);


    //brainAnimation(brain);
    // console.log("reached");

    // $(window).blur(function() {
    //clearInterval(brainAnim);
    //clearInterval(brainAnimInterval);
    // });

    // $(window).focus(function() {
    //brainAnimation(brain);
    // });


    $(sceneObj).on('growBrainEvent', function () {
        lastAnim = 1;
        //clearInterval(brainAnimInterval);
        //clearInterval(brainAnim);
        correctAnimation(brain);
        //brainAnimation(brain);
    });

    $(sceneObj).on('reset', function () {
        resetAnimation();
    });

    $(sceneObj).on('wrongEvent', function () {
        lastAnim = 2;
        //clearInterval(brainAnimInterval);
        //clearInterval(brainAnim);
        wrongAnimation();
        //brainAnimation(brain);
    });
    // debugger;

    $(sceneObj).on('startGame', function () {
        animator.once(function () {
            brain_location = sceneObj.add.image(530, 220, 'brain_indicator');
            isBrainLocatorVisible = true;
        }, 300, false);

        animator.once(function () {
            brain_location.destroy();
        }, 4000, false);

    });

    $(sceneObj).on('endingAnimation', function () {

        // hide timer
        sceneObj.countdown.hide();

        clipboard.destroy();
        crow.body.velocity.set(-600, 70);
        animator.once(function () {
            crow.flipX = true;
            crow.y += 200;
            crow.body.velocity.set(600, -120);
        }, 3000, false);

        animator.once(function () {
            crow.flipX = false;
            crow.y -= 50;
            crow.body.velocity.set(-700, 260);
        }, 6000, false);

        this.physics.add.overlap(crow, brainTable, function () {
            isTableFalling = true;
            brainTable.body.velocity.set(-130, 200);
            brain.body.velocity.set(0, -100);

            animator.once(function () {
                brain.body.velocity.set(0, 325);
            }, 200, false);

            animator.once(function () {
                einstein.body.velocity.set(0, -800);
            }, 850, false);

            animator.once(function () {
                brain.body.velocity.set(0, 0);
                starway.visible = false;
                starway_destroyed.visible = true;
                starway_destroyed.anims.play('starway_destroyed', false);
            }, 800, false);

            animator.once(function () {
                if (score > 0) {
                    spaceAnimation();
                }
            }, 2100, false);

            animator.once(function () {
                if (score <= 0) {
                    riverAnimation();
                }
            }, 1800, false);


            /*setTimeout(function() {
                brain.body.velocity.set(0, 325);
                setTimeout(function() {
                    einstein.body.velocity.set(0, -800);
                }, 650);
                setTimeout(function() {
                    brain.body.velocity.set(0,0);
                    starway.visible = false;
                    starway_destroyed.visible = true;
                    starway_destroyed.anims.play('starway_destroyed', false);

                    if(score > 0) {
                        setTimeout(spaceAnimation, 1300);
                    } else {
                        setTimeout(riverAnimation, 1000);
                    }

                }, 600);
            }, 200);*/
        }, null, this);
    });

    function riverAnimation() {
        einstein_sinking.displayWidth = 38.125;
        einstein_sinking.displayHeight = 70.75;


        einstein_sinking.visible = true;
        isEinsteinSinking = true;

        animator.once(function () {
            einstein_sinking.anims.play('einstein_sinking', false);
        }, 1000, false);

        animator.once(function () {
            isEinsteinSinking = false;
            showScore(score);
            addRestartButtons();
        }, 5000, false);

        /*	setTimeout(function() {
                einstein_sinking.anims.play('einstein_sinking', false);
            }, 1000);
            setTimeout(function() {
                isEinsteinSinking = false;
               showScore(score);
            }, 5000);*/
    }

    function spaceAnimation() {
        sceneObj.children.removeAll();
        sceneObj.add.image(400.5, -1580, 'sky');
        einstein_flying = sceneObj.physics.add.sprite(500, 210, 'einstein_flying');
        einstein_flying.anims.play('einstein_flying', false);
        isSkyCameraRoll = true;

        //let scrollYTo = score*6.490*50*10.5;
        /*if(isSkyCameraRoll) {
            this.cameras.main.scrollY -= 10.5;
            einstein_flying.y -= 10.5;
        }*/


        /*let scrollYBase = sceneObj.cameras.main.scrollY;
        let flyingYBase = einstein_flying.y;
         animator.start(function(perc) {
             sceneObj.cameras.main.scrollY = scrollYBase - scrollYTo * perc;
             einstein_flying.y = flyingYBase - scrollYTo * perc;
             /*lamp.displayHeight += riseByHeight * perc;
             lamp.displayWidth += riseByWidth * perc;
         },  6490*score, 0, null);*/

        var inSkyPause = 6100 * score;

        setTimeout(function () {
            isSkyCameraRoll = false;
            einstein_flying.anims.play('einstein-in-sky', true);
            einstein_sky_floating = true;
            einstein_flying.body.velocity.set(1, 15);
            showScore(score);
            addRestartButtons();
        }, inSkyPause);

/*        setTimeout(function () {
            addRestartButtons();
        }, inSkyPause + 500);*/

    }

    var transCnt = 0;

    /*function brainAnimation(brain) {
          let incWidth = (brain.displayWidth*0.1)/10;
        let incHeight = (brain.displayHeight*0.1)/10;
        brainAnimInterval = setInterval(function()  {
             transCnt = 0;
             brainAnim = setInterval(function() {
                 transCnt++;
                brain.displayWidth = transCnt <= 10 ? brain.displayWidth + incWidth:brain.displayWidth - incWidth;
                 brain.displayHeight = transCnt <= 10 ? brain.displayHeight + incHeight:brain.displayHeight - incHeight;
                 if(transCnt >= 20) {
                     clearInterval(brainAnim);
                 }

             }, 50);
        }, 1300);
      }*/

    function addRestartButtons(withBackground = false) {
        var buttonClass = '';
        if (withBackground) {
            buttonClass = 'withBackground';
        }
        if (!$('#restartGame').length) {
            $('#contentContainer').css('pointer-events', 'auto'); // also needed form some cases
            $('#question-field').remove(); // sometimes this element bothers
            // sometimes regular events are not working. so add them here
            var backToCourseButton = $('<div id="backToCourse" class="'+buttonClass+'"> </div>').on('click', function() {document.location = urlToCourse;});
            $('#html-container').append(backToCourseButton);
            var restartButton = $('<div id="restartGame" class="'+buttonClass+'"> </div>').on('click', function() {document.location = urlToRestart;});
            $('#html-container').append(restartButton);
        }
    }


    function wrongAnimation() {
        einstein.visible = false;
        einstein_mad = sceneObj.physics.add.sprite(680, 350, 'einstein_mad');
        einstein_mad.anims.play('mad', false);
    }

    function correctAnimation(brain) {

        //clearInterval(smirkInterval);
        einstein.visible = false;
        einstein_thumbs = sceneObj.physics.add.sprite(680, 350, 'einstein_thumbs');
        lamp = sceneObj.physics.add.sprite(680, 160, 'lamp');
        let riseByWidth = lamp.displayWidth * 0.01;
        let riseByHeight = lamp.displayHeight * 0.01;

        animator.start(function (perc) {
            lamp.displayHeight += riseByHeight * perc;
            lamp.displayWidth += riseByWidth * perc;
        }, 800, 0, null);

        animator.start(function (perc) {
            lamp.displayHeight -= riseByHeight * perc;
            lamp.displayWidth -= riseByWidth * perc;
        }, 800, 800, function () {
            lamp.destroy();
        }, 1300);

        //var shrinkTime;
        /*let lampInterval = setInterval(function() {
            lamp.displayHeight = shrinkTime ? lamp.displayHeight-incHeight : lamp.displayHeight+incHeight;
            lamp.displayWidth = shrinkTime ? lamp.displayWidth-incWidth : lamp.displayWidth+incWidth;
            if(lamp.displayWidth > 180)
                shrinkTime = 1;
            if(lamp.displayWidth < 125 && shrinkTime) {
                    clearInterval(lampInterval);
                    setTimeout(function() {
                        lamp.destroy();
                    }, 2000);
            }
        }, 20);*/

        cur_brain_width += (cur_brain_width / 2) * Math.pow(0.85, growCount);
        cur_brain_height += (cur_brain_height / 2) * Math.pow(0.85, growCount);
        growCount++;
        //cur_brain_inc_width = cur_brain_inc_width*1.4;
        //cur_brain_inc_height = cur_brain_inc_height*1.4;
        brain.displayWidth = cur_brain_width;
        brain.displayHeight = cur_brain_height;
        brain.y -= 7;
    }

    function resetAnimation() {
        switch (lastAnim) {
            case 1: //correct answer
                einstein_thumbs.destroy();
                break;
            case 2: //wrong answer
                einstein_mad.destroy();
                break;
        }
        einstein.visible = true;
    }

    requestAnimationFrame(animator.update);
    startingScreen = this.add.image(401, 300, 'startingScreen');
    $('#question-field').css('display', 'none');
    switch (BRAINGAME_TIMER_TYPE) {
        case 1:
            // questionnaire timer
            timerLabel = sceneObj.add.text(80, sceneObj.cameras.main.worldView.height - 80, formatDuration(BRAINGAME_TIMER_DURATION), {
                fontSize: "24px",
                fontFamily: "Luckiest Guy",
                fontWeight: "normal",
                fontStyle: "normal",
                fill: scoreLabelFillColor,
                // next code needed to fix doubled text with a regular font (not stable)
                stroke: scoreLabelStrokeColor,
                strokeThickness: 0
            });
            break;
        case 2:
            // global timer
            timerLabel = sceneObj.add.text(sceneObj.cameras.main.worldView.width - 120, sceneObj.cameras.main.worldView.y + 20, formatDuration(BRAINGAME_TIMER_DURATION), {
                fontSize: "36px",
                fontFamily: "Luckiest Guy",
                fontWeight: "normal",
                fontStyle: "normal",
                fill: scoreLabelFillColor,
                // next code needed to fix doubled text with a regular font (not stable)
                stroke: scoreLabelStrokeColor,
                strokeThickness: 0
            });
            break;
        case 0:
        default:
            // no timer. just empty text to ignore possible JS errors
            timerLabel = sceneObj.add.text(sceneObj.cameras.main.worldView.width - 120, sceneObj.cameras.main.worldView.y + 20, formatDuration(BRAINGAME_TIMER_DURATION), {
                fontSize: "36px",
                fontFamily: "Luckiest Guy",
                fontWeight: "normal",
                alpha: 0
            });

    }
    timerLabel.setVisible(false); // hide it by default
    this.countdown = new CountdownController(sceneObj, timerLabel)

}

function showScore(mdl_score) {
    scoreText = sceneObj.add.text(80, sceneObj.cameras.main.worldView.y + 100, 'Score: ' + ((score * 100).toFixed(2)), {
        fontSize: "52px",
        fontFamily: "Luckiest Guy",
        fontWeight: "400",
        fontStyle: "normal",
        fill: scoreLabelFillColor,
        // next code needed to fix doubled text with a regular font (not stable)
        stroke: scoreLabelStrokeColor,
        strokeThickness: 0
        // stroke: '#fff',
        // strokeThickness: 4
    });
}

function loadGameQuestions(gamedataurl) {
    // let gamedataurl = flashvars.gamedataurl;
    var ajaxCall = {
        url: gamedataurl,
        success: function (data, status, xhr) {
            let questionNodes = data.getElementsByTagName('questions')[0].childNodes;

            for (let i = 0; i < questionNodes.length; i++) {
                let answers = [];
                if (questionNodes[i].getAttribute('type') == 'multichoice') {
                    let answerNodes = questionNodes[i].childNodes[2].childNodes;
                    for (let j = 0; j < answerNodes.length; j++) {
                        let answer = answerNodes[j].childNodes;
                        answers.push({
                            id: answerNodes[j].getAttribute('id'),
                            text: answer[0].innerHTML,
                            isCorrect: Math.round(answer[0].parentElement.attributes[1].value) > 0 ? true : false,
                            fraction: answer[0].parentElement.attributes[1].value
                        });
                    }
                } else if (questionNodes[i].getAttribute('type') == 'truefalse') {
                    answers.push({
                            id: questionNodes[i].getAttribute('correctanswer') == '1' ? 1 : 0,
                            text: M.util.get_string('brain_istrue', 'mod_exagames'),
                            isCorrect: questionNodes[i].getAttribute('correctanswer') == '1' ? true : false,
                            fraction: questionNodes[i].getAttribute('correctanswer') == '1' ? 1.0 : 0.0
                        },
                        {
                            id: questionNodes[i].getAttribute('correctanswer') == '1' ? 0 : 1,
                            text: M.util.get_string('brain_isfalse', 'mod_exagames'),
                            isCorrect: questionNodes[i].getAttribute('correctanswer') == '1' ? false : true,
                            fraction: questionNodes[i].getAttribute('correctanswer') == '1' ? 0.0 : 1.0
                        });
                    if (questionNodes[i].getAttribute('randomizeanswers') == '1') {
                        // simple randomizing
                        answers.sort(() => Math.random() - 0.5);
                    }
                }
                let type = null;
                switch (questionNodes[i].getAttribute('type')) {
                    case 'multichoice':
                        if (questionNodes[i].getAttribute('single') == '1') {
                            type = 'multiple-choice-single-answer';
                        } else {
                            type = 'multiple-choice-multiple-answer';
                        }
                        break;
                    case 'truefalse':
                        type = 'multiple-choice-single-answer';
                        break;
                }
                questions.push({
                    answers: answers,
                    id: questionNodes[i].getAttribute('id'),
                    text: questionNodes[i].childNodes[0].innerHTML,
                    type: type
                });
            }

            console.log('questions loaded');
            gameInit();
        },
        error: function (xhr, status, error) {
            console.log("error questions loading");
        }
    };
    $.ajax(ajaxCall);
}

// just for debugging
/* let ajaxUrls = [];
 $.ajaxSetup({
     beforeSend: function (jqXHR, settings) {
         ajaxUrls.push(settings.url);
     },
     complete: function (jqXHR, settings, ttt) {
         const index = ajaxUrls.indexOf(settings.url);
         console.log('braingame.html:716');console.log(jqXHR);// !!!!!!!!!! delete it
         if (index > -1) {
             ajaxUrls.splice(index, 1);
         }
     }
 });*/

// On finishing all ajax requests, loaded all available questions
$(document).ajaxStop(function (e) {
    if (questions.length == 0) {
        alert(M.util.get_string('brain_noquestions', 'mod_exagames'));
        exit();
    }
    // console.log('braingame.html:714');console.log('some ajax done');// !!!!!!!!!! delete it
    // console.log('braingame.html:715');console.log(e);// !!!!!!!!!! delete it
    // console.log('All completed AJAX URLs:', ajaxUrls);
    // console.log(questions);
    questionUpdate();

});

$(document).ready(function() {
    $('body').on('click', '#restartGame', function(e) {
        e.preventDefault();
        document.location = urlToRestart;
    });
    $('body').on('click', '#backToCourse', function(e) {
        e.preventDefault();
        document.location = urlToCourse;
    });
})

// const EVALUATION_DISPLAY_DURATION = 4000;


var question1 = {
    text: "Wieviel ist 10x10",
    type: "multiple-choice-single-answer",
    answers: [{text: "1", isCorrect: false},
        {text: "10", isCorrect: false},
        {text: "100", isCorrect: true}]
}

var question2 = {
    text: "Wieviel ist 3x3",
    type: "multiple-choice-single-answer",
    answers: [{text: "3", isCorrect: false},
        {text: "9", isCorrect: true},
        {text: "27", isCorrect: false}]
}

//questions.push(question1);
//questions.push(question2);

function questionUpdate() {
    $('#contentContainer').css('pointer-events', '');

    if (round == questions.length) {
        $('#question-field').remove();
        return;
    }

    $('#question-field').empty();
    actQuestion = questions[round];
    $('#question-field').append('<div id="c-question"><p id="pQuestion">' + actQuestion.text + '</p></div>');

    if (actQuestion.type === 'multiple-choice-single-answer') {
        var domAppend = '<ul id="answerlist">';
        for (var i = 0; i < actQuestion.answers.length; i++) {
            if (actQuestion.answers[i].isCorrect) {
                domAppend += '<li id="answer' + i + '" class="alternative alternative-hover alternative-correct">' + actQuestion.answers[i].text + '<div class="correct-image">&nbsp;</div></li>';
            } else {
                domAppend += '<li id="answer' + i + '" class="alternative alternative-hover alternative-wrong">' + actQuestion.answers[i].text + '<div class="wrong-image">&nbsp;</div></li>';
            }
        }
        domAppend += '</ul>';
        $('#question-field').append(domAppend);
        $('.alternative').click(function (e) {
            handleAnswer(e, "single-choice");
        });
    } else if (actQuestion.type === 'multiple-choice-multiple-answer') {
        var domAppend = '<div id="answerlist">';
        for (var i = 0; i < actQuestion.answers.length; i++) {
            if (actQuestion.answers[i].fraction > 0) {
                domAppend += '<div class="answerContainer">' +
                    '<input type="checkbox" name="answerbox' + i + '" id="answerbox' + round + '_' + i + '" class="checkbox" value="' + actQuestion.answers[i].text + '"/> ' +
                    '<label for="answerbox' + round + '_' + i + '">' +
                    '<li id="answer' + i + '" class="alternative alternative-correct alternative-inline">' +
                    actQuestion.answers[i].text +
                    '<div class="correct-image">&nbsp;</div>' +
                    '</li>' +
                    '</label>' +
                    '</div>';
            } else {
                domAppend += '<div class="answerContainer">' +
                    '<input type="checkbox" name="answerbox' + i + '" id="answerbox' + round + '_' + i + '" class="checkbox" value="' + actQuestion.answers[i].text + '"/> ' +
                    '<label for="answerbox' + round + '_' + i + '">' +
                    '<li id="answer' + i + '" class="alternative alternative-wrong alternative-inline" >' +
                    actQuestion.answers[i].text +
                    '<div class="wrong-image">&nbsp;</div>' +
                    '</li>' +
                    '</label>' +
                    '</div>';
            }
        }
        domAppend += '</div>';
        $('#question-field').append(domAppend);
        $('#question-field').append('<button id="answerButton" class="btn btn-dark">' + M.util.get_string('brain_continue', 'mod_exagames') + '</button>');
        $('#answerButton').click(function (e) {
            handleAnswer(e, "multiple-choice");
        });
    }
    document.getElementById('question-field').scrollTop = 0;
}

function handleAnswer(e, type, forceScore = -1) {
    $('#contentContainer').css('pointer-events', 'none');
    if (type === 'single-choice') {
        var answerId = null;
        if (e != null) {
            var answerId = e.currentTarget.id.substr(e.currentTarget.id.length - 1);
        }
        singleAlternativeHandler(e, answerId, forceScore);
    } else if (type === 'multiple-choice') {
        multipleAlternativesHandler(e, forceScore);
    }
}

function singleAlternativeHandler(e, answerId, forceScore = -1) {
    if (forceScore == -1) {
        if (e !== null) {
            sceneObj.countdown.stop();
            sceneObj.countdown.stopBlinking();
            // real answer
            let answText = $(e.currentTarget).text();
            var curAnswerId = null;
            for (let i = 0; i < questions[round].answers.length; i++) {
                if (questions[round].answers[i].text == answText) {
                    curAnswerId = questions[round].answers[i].id;
                    break;
                }
            }

            answers += "&responses[" + questions[round].id + "]=" + curAnswerId;
            var liElem = $(e.currentTarget);
            if (liElem.hasClass("alternative-correct")) {
                liElem.find('.correct-image').css('background-image', 'url(html5/braingame/assets/checked.png)');
                $(sceneObj).trigger('growBrainEvent');
                var tScore = (1 / questions.length) * questions[round].answers[answerId].fraction;
                score += tScore;
            } else {
                liElem.find('.wrong-image').css('background-image', 'url(html5/braingame/assets/cancel.png)');
                $(sceneObj).trigger('wrongEvent');
            }

        } else {
            // triggered by ended timer
            score += 0;
        }
        selectedAlternativeAnimation();

    } else {
        // use fixed score value
        score += forceScore;
    }

    round++;

    if (round == questions.length) {
        // stop the timer
        sceneObj.countdown.stop();
        // send result to the server
        storeResultScore(function () {
            $('#question-field').empty();
            $(sceneObj).trigger('endingAnimation');
        });
    }

    if (forceScore == -1) {
        setTimeout(function () {
            questionUpdate();
            $(sceneObj).trigger('reset');
            sceneObj.countdown.start(manageCountdownEnded.bind(), BRAINGAME_TIMER_DURATION);
        }, EVALUATION_DISPLAY_DURATION);
    }

}


function multipleAlternativesHandler(e, forceScore = -1) {
    var tempScore = 0;
    if (forceScore == -1) {
        sceneObj.countdown.stop();
        sceneObj.countdown.stopBlinking();
        var checkedBoxes = getCheckedBoxes();
        var curAnswerIds = "";
        var parser = new DOMParser;
        var maxScoreForQuestion = 0;
        for (let i = 0; i < questions[round].answers.length; i++) {
            let frac = Number(questions[round].answers[i].fraction);
            if (frac > 0.0) {
                maxScoreForQuestion += Number(questions[round].answers[i].fraction);
            }
        }
        // console.log('maxScoreForQuestion = ' + maxScoreForQuestion);
        var wrongDecreaser = maxScoreForQuestion / checkedBoxes.length;
        for (let i = 0; i < checkedBoxes.length; i++) {
            var box = checkedBoxes[i];
            if (box.wasChecked) {
                let answText = box.node[0].textContent;
                for (let i = 0; i < questions[round].answers.length; i++) {
                    if (parser.parseFromString(questions[round].answers[i].text, 'text/html').body.textContent == answText) {
                        if (curAnswerIds != "") {
                            curAnswerIds += ",";
                        }
                        curAnswerIds += questions[round].answers[i].id;
                        break;
                    }
                }

                var file;
                // console.log('fraction val =' + questions[round].answers[i].fraction);
                if (questions[round].answers[i].fraction > 0) {
                    // it is correct (partially or fully) answer
                    tempScore += Number(questions[round].answers[i].fraction) / maxScoreForQuestion;
                } else {
                    // it is wrong answer - decrease the score
                    tempScore -= wrongDecreaser;
                }

                if (box.alternativeState == true) {
                    file = "checked.png"
                } else {
                    file = "cancel.png";
                }
                $('#' + box.node[0].id).find('div').css('background-image', 'url(/mod/exagames/html5/braingame/assets/' + file + ')');
            }
        }
    } else {
        // use needed answer value
        tempScore = forceScore;
    }
    if (tempScore < 0) {
        tempScore = 0;
    }
    if (tempScore > 0.0000000) {
        $(sceneObj).trigger('growBrainEvent');
    }

    score += (1 / questions.length) * tempScore;
    answers += "&responses[" + questions[round].id + "]=" + curAnswerIds;

    selectedAlternativeAnimation();
    round++;

    if (round == questions.length) {
        // stop the timer
        sceneObj.countdown.stop();
        // send result to the server
        storeResultScore(function () {
            $('#question-field').empty();
            $(sceneObj).trigger('endingAnimation');
        });
    }

    if (forceScore == -1) {
        setTimeout(function () {
            questionUpdate();
            $(sceneObj).trigger('reset');
            sceneObj.countdown.start(manageCountdownEnded.bind(), BRAINGAME_TIMER_DURATION);
        }, EVALUATION_DISPLAY_DURATION);
    }

}

function getCheckedBoxes() {
    var selected = [];
    $('#answerlist').find('.checkbox').each(function () {
        var liElem = $(this).closest('.answerContainer').find('.alternative');
        var node = $(this).next();

        selected.push({
            node: node,
            alternativeState: liElem.hasClass("alternative-correct"),
            wasChecked: $(this).prop('checked')
        });
    });
    return selected;
}

function selectedAlternativeAnimation() {
    $('.alternative-wrong').addClass('alternative-reveal-wrong');
    $('.alternative-correct').addClass('alternative-reveal-correct');
    var margin = 2.5;
    var animWrongAlternatives = setInterval(function () {
        $('.alternative-wrong').css('margin-left', margin + 'px');
        margin += 2.5;
        if (margin >= 35) {
            clearInterval(animWrongAlternatives);
        }
    }, 40);

}

function isMultiChoiceMultiAnswersType(answers) {
    for (let i = 0; i < answers.length; i++) {
        if (!(answers[i].fraction == 0.0000000 || answers[i].fraction == 1.0000000)) {
            return true;
        }
    }
    return false;
}

function storeResultScore(callback) {
    $.ajax({
        url: (flashvars.gameurl + "&action=data&score=" + score.toFixed(2)),
        type: 'POST',
        error: function (xhr, status, error) {
            callback();
        },
        success: function (data) {
            callback();
        }
    });
}

class CountdownController {
    /** @type {Phaser.Scene} */
    scene
    /** @type {Phaser.GameObjects.Text} */
    label
    /** @type {Phaser.Time.TimerEvent} */
    timerEvent
    duration = 0
    blinkTween
    timerLabelBlinked

    /**
     *
     * @param {Phaser.Scene} scene
     * @param {Phaser.GameObjects.Text} label
     */
    constructor(scene, label)
    {
        this.scene = scene
        this.label = label
    }

    /**
     * @param {() => void} callback
     * @param {number} duration
     */
    start(callback, duration = 45000)
    {
        this.stop()

        this.finishedCallback = callback
        this.duration = duration

        // reset color
        timerLabel.setFill(scoreLabelFillColor);
        this.stopBlinking();

        this.timerEvent = this.scene.time.addEvent({
            delay: duration,
            callback: () => {
                this.label.text = '00:00'
                this.stop()
                if (callback) {
                    callback()
                }
            }
        })
    }

    stop()
    {
        if (this.timerEvent)
        {
            this.timerEvent.destroy()
            this.timerEvent = undefined
        }
    }

    hide() {
        this.stopBlinking();
        timerLabel.setAlpha(0);
    }

    update()
    {
        if (!this.timerEvent || this.duration <= 0)
        {
            return
        }

        const elapsed = this.timerEvent.getElapsed()
        const remaining = this.duration - elapsed

        var timetoblink = 15000;
        if (BRAINGAME_TIMER_TYPE == 2) {
            // global timer
            timetoblink = 60000;
        }

        if (remaining < timetoblink) {
            this.startBlinking();
        }

        this.label.text = formatDuration(remaining);
    }

    startBlinking()
    {
        if (!this.timerLabelBlinked) {
            // BLINK it
            this.timerLabelBlinked = true;
            this.blinkTween = sceneObj.tweens.add({
                targets: timerLabel,
                alpha: 0, // Fade out completely
                ease: 'Linear',
                duration: 500, // Time it takes to fade out
                yoyo: true, // Fade back in
                repeat: -1 // Repeat indefinitely
            });
            // make it red!
            timerLabel.setStyle({
                fill: timerLabelBlinkingFillColor
            });
        }
    }

    stopBlinking()
    {
        if (this.timerLabelBlinked) {
            this.timerLabelBlinked = false;
            if (this.blinkTween) {
                this.blinkTween.stop(); // Stop the tween
                timerLabel.setAlpha(1); // Reset the text to full visibility
            }
        }
    }

}

// milliseconds to human value!
function formatDuration(ms) {
    var minutes = Math.floor(ms / 60000);
    var seconds = ((ms % 60000) / 1000).toFixed(0);
    var resultStr = (
        seconds == 60 ?
        (minutes + 1) + ":00" :
        (minutes < 10 ? "0" + minutes : minutes) + ":" + (seconds < 10 ? "0" : "") + seconds
    );
    return resultStr;
}

// the timer ended!
function manageCountdownEnded()
{

    switch (BRAINGAME_TIMER_TYPE) {
        case 1:
            // the timer is a question timer
            actQuestion = questions[round];
            if (typeof actQuestion !== 'undefined') {
                if (actQuestion.type == 'multiple-choice-single-answer') {
                    handleAnswer(null, "single-choice", -1);
                } else if (actQuestion.type == 'multiple-choice-multiple-answer') {
                    handleAnswer(null, "multiple-choice", -1);
                }
            }
            break;
        case 2:
            // the timer is a global timer - end current question as a WRONG answer and calculate all other answers as wrong!
            $('#question-field').empty();
            while (round < questions.length) {
                actQuestion = questions[round];
                if (actQuestion.type == 'multiple-choice-single-answer') {
                    handleAnswer(null, "single-choice", 0);
                } else if (actQuestion.type == 'multiple-choice-multiple-answer') {
                    handleAnswer(null, "multiple-choice", 0);
                }
                // round++; // it is increased later - in handleAnswer func
            }
            // Increase timer size by scaling - from right top corner
            /*let scaleFactor = 1.5;
            let rightTopX = timerLabel.x + timerLabel.width;
            let rightTopY = timerLabel.y;
            timerLabel.setScale(scaleFactor);
            timerLabel.x = rightTopX - (timerLabel.width * scaleFactor);
            timerLabel.y = rightTopY;*/
            // "wrong event" event
            $(sceneObj).trigger('wrongEvent');
            var toReset = setInterval(function () {
                $(sceneObj).trigger('reset');
            }, 2000);
            break;
        case 0:
        default:
           // no timer
    }

    return ;
}