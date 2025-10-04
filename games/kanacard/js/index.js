//単語とかなと絵を組み合わせて配列にする
$(document).ready(function () {
  console.log('jQuery loaded:', typeof $ !== 'undefined');
  console.log('DOM ready, starting kana card game...');
  const allCards = {
    a: [
      { word: "あさがお", kana: "あ", img: "img/img_e/asagao.png" },
      { word: "いぬ", kana: "い", img: "img/img_e/inu.png" },
      { word: "うさぎ", kana: "う", img: "img/img_e/usagi.png" },
      { word: "えのぐ", kana: "え", img: "img/img_e/enogu.png" },
      { word: "おにぎり", kana: "お", img: "img/img_e/onigiri.png" },
    ],
    ka: [
      { word: "かぶとむし", kana: "か", img: "img/img_e/kabutomushi.png" },
      { word: "きつね", kana: "き", img: "img/img_e/kitsune.png" },
      { word: "くま", kana: "く", img: "img/img_e/kuma.png" },
      { word: "けしごむ", kana: "け", img: "img/img_e/keshigomu.png" },
      { word: "こくばん", kana: "こ", img: "img/img_e/kokuban.png" },
    ],
    sa: [
      { word: "さい", kana: "さ", img: "img/img_e/sai.png" },
      { word: "しんかんせん", kana: "し", img: "img/img_e/shinkansen.png" },
      { word: "すいか", kana: "す", img: "img/img_e/suika.png" },
      { word: "せみ", kana: "せ", img: "img/img_e/semi.png" },
      { word: "そら", kana: "そ", img: "img/img_e/sora.png" },
    ],
    ta: [
      { word: "たまご", kana: "た", img: "img/img_e/tamago.png" },
      { word: "ちず", kana: "ち", img: "img/img_e/chizu.png" },
      { word: "つくえ", kana: "つ", img: "img/img_e/tsukue.png" },
      { word: "てんとうむし", kana: "て", img: "img/img_e/tentoumushi.png" },
      { word: "とけい", kana: "と", img: "img/img_e/tokei.png" },
    ],
    na: [
      { word: "なわとび", kana: "な", img: "img/img_e/nawatobi.png" },
      { word: "にんじん", kana: "に", img: "img/img_e/ninjin.png" },
      { word: "ぬいぐるみ", kana: "ぬ", img: "img/img_e/nuigurumi.png" },
      { word: "ねこ", kana: "ね", img: "img/img_e/neko.png" },
      { word: "のり", kana: "の", img: "img/img_e/nori.png" },
    ],
    ha: [
      { word: "はさみ", kana: "は", img: "img/img_e/hasami.png" },
      { word: "ひこうき", kana: "ひ", img: "img/img_e/hikouki.png" },
      { word: "ふうせん", kana: "ふ", img: "img/img_e/fuusen.png" },
      { word: "へび", kana: "へ", img: "img/img_e/hebi.png" },
      { word: "ほし", kana: "ほ", img: "img/img_e/hoshi.png" },
    ],
    ma: [
      { word: "まめ", kana: "ま", img: "img/img_e/mame.png" },
      { word: "みかん", kana: "み", img: "img/img_e/mikan.png" },
      { word: "むしめがね", kana: "む", img: "img/img_e/mushimegane.png" },
      { word: "めがね", kana: "め", img: "img/img_e/megane.png" },
      { word: "もも", kana: "も", img: "img/img_e/momo.png" },
    ],
    ya: [
      { word: "やさい", kana: "や", img: "img/img_e/yasai.png" },
      { word: "ゆびわ", kana: "ゆ", img: "img/img_e/yubiwa.png" },
      { word: "ようふく", kana: "よ", img: "img/img_e/youfuku.png" },
    ],
    ra: [
      { word: "らくだ", kana: "ら", img: "img/img_e/rakuda.png" },
      { word: "りす", kana: "り", img: "img/img_e/risu.png" },
      { word: "るすばん", kana: "る", img: "img/img_e/rusuban.png" },
      { word: "れいぞうこ", kana: "れ", img: "img/img_e/reizouko.png" },
      { word: "ろうそく", kana: "ろ", img: "img/img_e/rousoku.png" },
    ],
    wa: [{ word: "わに", kana: "わ", img: "img/img_e/wani.png" }],
  };
  //配列の中は[A, B, C]で囲む 文字列の場合["りんご", "いちご", "バナナ"]
  //それをさらにグループ化{ word:"____", kana:"____", img:"相対パスで場所を表示"}
  // console.log(allCards);

  let correctKana = ""; //最初は見えないようにしておく
  let lastCard = null; // 前回のカードを記録
  let score = 0; // 点数管理
  let firstAttempt = true; // 1回目の挑戦かどうか

  let cards = allCards["a"]; // 最初は「あ」行

  // メニューボタンのクリックで行切り替え
  $(".menu_btn").on("click", function () {
    const group = $(this).data("group");
    currentGroup = group;
    //console.log(group);

    $(".menu_btn").removeClass("active"); // 今やっているところに色をつける
    $(this).addClass("active");

    if (group === "all") {
      cards = Object.values(allCards).flat(); // 全グループをまとめる
    } else {
      cards = allCards[group]; //そうではない時はそれぞれのグループでくくる
    }
    lastCard = null; // グループ変更時は前回カードをリセット
    setNewQuestion();
  });

  const kanaGroups = {
    a: ["あ", "い", "う", "え", "お"],
    ka: ["か", "き", "く", "け", "こ"],
    sa: ["さ", "し", "す", "せ", "そ"],
    ta: ["た", "ち", "つ", "て", "と"],
    na: ["な", "に", "ぬ", "ね", "の"],
    ha: ["は", "ひ", "ふ", "へ", "ほ"],
    ma: ["ま", "み", "む", "め", "も"],
    ya: ["や", "ゆ", "よ"],
    ra: ["ら", "り", "る", "れ", "ろ"],
    wa: ["わ", "を", "ん"],
  };

  let currentGroup = "a";

  // 点数をローカルストレージから読み込み
  function loadScore() {
    const savedScore = localStorage.getItem('kanacard_score');
    score = savedScore ? parseInt(savedScore) : 0;
    updateScoreDisplay();
  }

  // 点数を保存
  function saveScore() {
    localStorage.setItem('kanacard_score', score.toString());
  }

  // 点数表示を更新
  function updateScoreDisplay() {
    $('#current_score').text(score);
  }

  // 点数を加算
  function addScore(points) {
    score += points;
    updateScoreDisplay();
    saveScore();
  }

  //問題
  function setNewQuestion() {
    firstAttempt = true; // 新しい問題なので1回目の挑戦
    //ランダム処理（重複回避）
    let random;
    let attempts = 0;
    
    // カードが2枚以上ある場合のみ重複チェック
    if (cards.length > 1) {
      do {
        random = cards[Math.floor(Math.random() * cards.length)];
        attempts++;
      } while (lastCard && random.word === lastCard.word && attempts < 10);
    } else {
      random = cards[Math.floor(Math.random() * cards.length)];
    }
    
    lastCard = random; // 選択したカードを記録
    console.log('Random card selected:', random);
    correctKana = random.kana;
    console.log('Correct kana:', correctKana);

    //絵をrandomのimgに替える cf.attribute-属性
    const $img = $(".card_wrap img");
    console.log('Updating image element:', $img.length);
    $img.attr("src", random.img);
    console.log('New image src:', $img.attr("src"));

    //wordの最初の文字を飛ばして2文字目(1)から取り出す
    const restOfWord = random.word.substring(1);
    console.log('Rest of word:', restOfWord);

    //restOfWordの前に?をつけて.question_wordに表示
    const $questionWord = $(".question_word");
    console.log('Question word element found:', $questionWord.length);
    $questionWord.html(`<span class="hatena">？</span>${restOfWord}`);
    console.log('Question word updated:', $questionWord.html());

    // 選択肢を更新
    let options = [];

    if (currentGroup === "all") {
      // ランダムモードのとき
      const allKana = Object.values(kanaGroups).flat();
      // kanaGroups からすべてのひらがな（あ〜ん）をひとつの配列にまとめる

      const others = allKana.filter((k) => k !== correctKana);
      // 正解のかな（correctKana）を除いた文字だけ残す → 間違いの候補を作っている

      const shuffled = others.sort(() => 0.5 - Math.random());
      // シャッフルしてランダムな並びにする（sort でランダムソート）

      options = [correctKana, ...shuffled.slice(0, 4)];
      // 正解＋間違い4つのkana ＝ 合計5個の選択肢を作る

      options = options.sort(() => 0.5 - Math.random());
      // 選択肢をもう一度ランダムに並べ替える（正解が真ん中に来たりするように）
    } else {
      // 「あ行」や「か行」など固定の行が選ばれている場合
      options = kanaGroups[currentGroup];
      // 例: kanaGroups["ka"] → ["か", "き", "く", "け", "こ"]
    }

    console.log('Generated options:', options);
    renderKanaChoices(options);
  }
  // 文字を表示（liを生成）
  function renderKanaChoices(kanaArray) {
    console.log('Rendering kana choices:', kanaArray);
    const $ul = $(".select_choices");
    console.log('Found select_choices element:', $ul.length);
    $ul.empty();
    kanaArray.forEach((kana) => {
      const $li = $("<li>").addClass("box2_1").text(kana);
      $ul.append($li);
    });
    console.log('Added choices to DOM, total:', $ul.find('li').length);

     $(".select_choices li").removeClass("active-choice");
  }

  //判定-クリックイベント
  // より確実なイベント委譲
  $(document).on("click", ".select_choices li", function () {
    console.log('Choice clicked!');
    
    // 他の選択肢からactive-choiceを外す
    $(".select_choices li").removeClass("active-choice");
    // 今クリックした要素にだけつける
    $(this).addClass("active-choice");

    const selectedKana = $(this).text().trim();
    console.log('Selected kana:', selectedKana);
    console.log('Correct kana:', correctKana);
    console.log('Match?', selectedKana === correctKana);

    const $message = $("#judge_message");
    console.log('Judge message element found:', $message.length);
    console.log('Message element:', $message[0]);

    //judgement
    if (selectedKana === correctKana) {
      console.log('Correct answer! Showing success message...');
      
      // 1回目の正解で1点獲得
      if (firstAttempt) {
        addScore(1);
        $message
          .text("⭕ せいかい！ +1点")
          .removeClass("incorrect")
          .addClass("correct")
          .css('display', 'block');
      } else {
        $message
          .text("⭕ せいかい！")
          .removeClass("incorrect")
          .addClass("correct")
          .css('display', 'block');
      }
      
      console.log('Success message should be visible now');
      console.log('Message display style:', $message.css('display'));
      console.log('Message text:', $message.text());
      
      setTimeout(() => {
        console.log('Hiding message and setting new question');
        $message.css('display', 'none');
        setNewQuestion();
      }, 1500);
        
    } else {
      console.log('Wrong answer! Showing try again message...');
      firstAttempt = false; // 間違えたので1回目ではなくなる
      $message
        .text("❌ もういっかい！")
        .removeClass("correct")
        .addClass("incorrect")
        .css('display', 'block');
        
      console.log('Try again message should be visible now');
      console.log('Message display style:', $message.css('display'));
      console.log('Message text:', $message.text());
      
      setTimeout(() => {
        console.log('Hiding try again message');
        $message.css('display', 'none');
      }, 1200);
    }
  });

  // あそびかたボタンのクリックイベント
  $('#help_button').on('click', function() {
    const $rules = $('#rules');
    
    if ($rules.hasClass('show')) {
      $rules.removeClass('show');
    } else {
      $rules.addClass('show');
    }
  });

  // 閉じるボタンのクリックイベント
  $('#close_rules').on('click', function() {
    $('#rules').removeClass('show');
  });

  // 初期化
  console.log('Initializing kana card game...');
  
  // 点数を読み込み
  loadScore();
  
  // DOM要素が存在するか確認
  console.log('Card wrap image exists:', $('.card_wrap img').length);
  console.log('Select choices exists:', $('.select_choices').length);
  console.log('Question word exists:', $('.question_word').length);
  
  // 少し遅延してから初期化
  setTimeout(function() {
    setNewQuestion();
    
    // テスト用：直接クリックイベントもバインド
    $('.box2_1').click(function() {
      console.log('Direct click event triggered!');
    });
  }, 100);
});
