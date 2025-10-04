<?php
// school_stories/index.php - がっこうのこと
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/language.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../auth/login.php');
    exit;
}

$user = $_SESSION['user'];
// 言語設定（言語管理システムを使用）
$current_language = $lang->getLanguage();

// ページタイトル翻訳
$page_titles = [
    'ja' => 'がっこうのこと - nihongonote',
    'en' => 'About School - nihongonote', 
    'zh' => '关于学校 - nihongonote',
    'tl' => 'Buhay sa Paaralan - nihongonote'
];

$page_title = $page_titles[$current_language];

// 学校関連の記事データ
// 多言語コンテンツ
$multilingualContent = [
    1 => [ // 日本の学校について
        'en' => [
            'title' => 'About Your School',
            'description' => 'A Summary of School Life in Japan',
            'content' => '
                <p>The education system in Japan is called the "6-3-3-4" system.</p>
                <p>Elementary school is for 6 years, junior high school is for 3 years, high school is for 3 years, and university is for 4 years, so it is called "6-3-3-4."</p>
                
                <p>Elementary and junior high school are "compulsory education."</p>
                <p>After graduating from high school, some students choose to go to universities, colleges, junior colleges or vocational schools.</p>
                
                <p>Japanese academic year starts in April and ends in March of the following year.</p>
                <p>Elementary, junior high, and high school usually have two or three "terms" in a year.</p>
                <p>A term is a period in which classes are held. There are long breaks between these terms.</p>
                
                <div style="margin: 20px 0; text-align: center;">
                    <div style="display: inline-block; background: white; border: 2px solid #333; padding: 15px; max-width: 600px; width: 100%;">
                        <div style="display: flex; align-items: center; margin-bottom: 10px;">
                            <div style="flex: 8.5; background: #ffeb3b; color: #333; text-align: center; padding: 5px 15px; font-weight: bold; border: 1px solid #333;">Compulsory Education</div>
                            <div style="flex: 7.5;"></div>
                        </div>
                        <div style="display: flex; align-items: stretch; margin-bottom: 10px;">
                            <div style="flex: 6; background: #ff5050; color: white; text-align: center; padding: 15px; font-weight: bold; border: 1px solid #333;">Elementary</div>
                            <div style="flex: 3; background: #ff8080; color: white; text-align: center; padding: 15px; font-weight: bold; border: 1px solid #333;">Junior High</div>
                            <div style="flex: 3; background: #ffb3b3; color: #333; text-align: center; padding: 15px; font-weight: bold; border: 1px solid #333;">High School</div>
                            <div style="flex: 4; background: #ffe6e6; color: #333; text-align: center; padding: 15px; font-weight: bold; border: 1px solid #333;">University</div>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <div style="flex: 6; text-align: center; font-weight: bold;">6 years</div>
                            <div style="flex: 3; text-align: center; font-weight: bold;">3 years</div>
                            <div style="flex: 3; text-align: center; font-weight: bold;">3 years</div>
                            <div style="flex: 4; text-align: center; font-weight: bold;">4 years</div>
                        </div>
                    </div>
                </div>
            '
        ],
        'zh' => [
            'title' => '学校的故事',
            'description' => '整理了日本学校生活相关信息',
            'content' => '
                <p>日本的学校制度是6、3、3、4制。</p>
                <p>小学6年，初中3年，高中3年，大学4年的6、3、3、4。</p>
                
                <p>小学和初中是"义务教育"。</p>
                <p>也有高中毕业后去大学、短期大学或专门学校的学生。</p>
                
                <p>日本的学校从4月开始，第二年3月结束。</p>
                <p>一般在小学、初中、高中，一年有2或3个"学期"。</p>
                <p>学期是指上课的期间。三个学期之间有长假。</p>
                
                <div style="margin: 20px 0; text-align: center;">
                    <div style="display: inline-block; background: white; border: 2px solid #333; padding: 15px; max-width: 600px; width: 100%;">
                        <div style="display: flex; align-items: center; margin-bottom: 10px;">
                            <div style="flex: 8.5; background: #ffeb3b; color: #333; text-align: center; padding: 5px 15px; font-weight: bold; border: 1px solid #333;">义务教育</div>
                            <div style="flex: 7.5;"></div>
                        </div>
                        <div style="display: flex; align-items: stretch; margin-bottom: 10px;">
                            <div style="flex: 6; background: #ff5050; color: white; text-align: center; padding: 15px; font-weight: bold; border: 1px solid #333;">小学校</div>
                            <div style="flex: 3; background: #ff8080; color: white; text-align: center; padding: 15px; font-weight: bold; border: 1px solid #333;">中学校</div>
                            <div style="flex: 3; background: #ffb3b3; color: #333; text-align: center; padding: 15px; font-weight: bold; border: 1px solid #333;">高等学校</div>
                            <div style="flex: 4; background: #ffe6e6; color: #333; text-align: center; padding: 15px; font-weight: bold; border: 1px solid #333;">大学</div>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <div style="flex: 6; text-align: center; font-weight: bold;">6年</div>
                            <div style="flex: 3; text-align: center; font-weight: bold;">3年</div>
                            <div style="flex: 3; text-align: center; font-weight: bold;">3年</div>
                            <div style="flex: 4; text-align: center; font-weight: bold;">4年</div>
                        </div>
                    </div>
                </div>
            '
        ],
        'tl' => [
            'title' => 'Buhay sa paaralan',
            'description' => 'Buod ng Buhay sa Paaralan sa Japan',
            'content' => '
                <p>Ang sistema ng edukasyon ng Japan ay tinatawag na "6-3-3-4".</p>
                <p>Anim na taon sa elementarya, tatlong taon sa junior high school, tatlong taon sa high school, at apat na taon sa unibersidad. Kaya tinatawag itong "6-3-3-4".</p>
                
                <p>Ang elementarya at junior high school ay "sapilitang edukasyon" (compulsory education).</p>
                <p>Pagkatapos magtapos ng high school, may ilang estudyante na pumipiling pumasok sa unibersidad, kolehiyo, junior college, o mga paaralang bokasyonal.</p>
                
                <p>Nagsisimula ang taong pampaaralan sa Japan tuwing Abril at nagtatapos sa Marso ng susunod na taon.</p>
                <p>Sa elementarya, junior high school, at high school, karaniwan ay may dalawa o tatlong semestre sa isang taon.</p>
                <p>Ang semestre ay ang panahon kung kailan may klase. Mayroong mahahabang bakasyon sa pagitan ng mga semestre.</p>
                
                <div style="margin: 20px 0; text-align: center;">
                    <div style="display: inline-block; background: white; border: 2px solid #333; padding: 15px; max-width: 600px; width: 100%;">
                        <div style="display: flex; align-items: center; margin-bottom: 10px;">
                            <div style="flex: 8.5; background: #ffeb3b; color: #333; text-align: center; padding: 5px 15px; font-weight: bold; border: 1px solid #333;">Sapilitang Edukasyon</div>
                            <div style="flex: 7.5;"></div>
                        </div>
                        <div style="display: flex; align-items: stretch; margin-bottom: 10px;">
                            <div style="flex: 6; background: #ff5050; color: white; text-align: center; padding: 15px; font-weight: bold; border: 1px solid #333;">Elementarya</div>
                            <div style="flex: 3; background: #ff8080; color: white; text-align: center; padding: 15px; font-weight: bold; border: 1px solid #333;">Junior High</div>
                            <div style="flex: 3; background: #ffb3b3; color: #333; text-align: center; padding: 15px; font-weight: bold; border: 1px solid #333;">High School</div>
                            <div style="flex: 4; background: #ffe6e6; color: #333; text-align: center; padding: 15px; font-weight: bold; border: 1px solid #333;">Unibersidad</div>
                        </div>
                        <div style="display: flex; align-items: center;">
                            <div style="flex: 6; text-align: center; font-weight: bold;">6 taon</div>
                            <div style="flex: 3; text-align: center; font-weight: bold;">3 taon</div>
                            <div style="flex: 3; text-align: center; font-weight: bold;">3 taon</div>
                            <div style="flex: 4; text-align: center; font-weight: bold;">4 taon</div>
                        </div>
                    </div>
                </div>
            '
        ]
    ],
    3 => [ // 家庭訪問
        'ja' => [
            'title' => '家庭訪問',
            'description' => '先生が家庭を訪問する制度について',
            'content' => '
                <p>先生が、各家庭に行って家で保護者とお話をします。先生が家に行く時間には必ず保護者は家にいてください。事前に都合のよい時間を聞いて時間を調整します。</p>
                <p>先生は計画を立てて、1日に何軒かまわります。そのため、1つの家庭でお話する時間は決まっています。時間を延ばすことはできません。あらかじめ、先生に相談したいことがある場合は内容をまとめておきましょう。</p>
                <p>先生は、保護者とお話するだけではなく、子どもの家庭での生活の様子を見たり、家がある場所を確認します。家の中に入ってお話をする場合や玄関でお話をする場合があります。</p>
                <p>おみやげやお茶、おかし、食事などを出す必要はありません。</p>
                <p>長期欠席や連絡がとれない場合にも家庭訪問をすることがあります。</p>
            '
        ],
        'en' => [
            'title' => 'Home visit (kateihoumon)',
            'description' => 'About the system where teachers visit students\' homes',
            'content' => '
                <p>The teacher conducts home visits to each family\'s home to talk to the guardians. The guardians must be at home during the time when the teacher comes visiting. A suitable time is arranged for both parties beforehand.</p>
                <p>The teacher has to follow a schedule for the day when he/she has several visits. As such, the amount of time allocated for each family is fixed. It is not possible to extend the time. Summarize the content of the things you want to talk to the teacher about in advance.</p>
                <p>The teacher does not only talk to the guardians but also observe the child in his/her own home environment, as well as confirming the location of the house. The talk can be conducted either inside the house or at the entrance of the house.</p>
                <p>It is not necessary to take out presents, drinks, snacks, food, etc.</p>
                <p>The home visits may also conducted for those with long absenteeism.</p>
            '
        ],
        'zh' => [
            'title' => '家庭访问',
            'description' => '关于老师访问学生家庭的制度',
            'content' => '
                <p>老师去各个家庭，在学生的家里和家长谈话。老师去的时候，请家长一定要在家。事先问问老师的时间，然后调整自己的时间。</p>
                <p>老师会事先做好计划，一天要去访问好几家。所以，在一个家庭谈话的时间是安排好的，不能延长时间。如果有想跟老师说的事，请提前想好。</p>
                <p>老师的访问，不光是为了和家长谈话，也为了看看孩子在家里的生活样子，确认每一个孩子的住所。有时候会到家里面去谈话，有时候只在门口说话。</p>
                <p>不需要给老师准备礼物、茶水、点心、饭菜等。</p>
                <p>即使长期缺勤或联系不上的孩子，老师有时候也会去访问的。</p>
            '
        ],
        'tl' => [
            'title' => 'Pagbisita ng guro sa bahay',
            'description' => 'Tungkol sa sistema ng pagbisita ng guro sa tahanan',
            'content' => '
                <p>Dadalaw ang guro sa bawat tahanan o bahay ng mga bata. Siguraduhing nasa bahay ang magulang sa nakatakdang oras ng pagdalaw ng nito. Isasaayos at iaawas ng guro ang oras sa magulang kung kelan puwedeng makadalaw ito sa tahanan o bahay.</p>
                <p>Ang nakatakdang petsa at oras ay ipapaalam sa magulang bago dumalaw sa bahay. At dahil sa mga ilang bahay ang mapupuntahan ng guro sa loob ng isang araw, may takdang oras ng pamamalagi ang guro. Hindi ito puwedeng magtagal sa isang bahay, kung kayat ihanda na agad ang mga nais na itanong o sasabihin sa guro.</p>
                <p>Ang pakay ng guro ay hindi lamang para kausapin ang magulang o tagapag-alaga kundi aalamin din nito kung nasa anong sitwasyon ang pamumuhay ng bata at para malaman kung saang lugar ito nakatira. May pagkakataon na papasok ang guro sa loob ng bahay at may pagkakataon naman na sa may pintuan ng bahay lang ito makikipag-usap.</p>
                <p>Hindi na kailangang magabala sa paghahanda ng pagkain o inumin sa araw na ito.</p>
                <p>Kapag ang bata ay matagal ng hindi pumapasok sa klase, may posibilidad na puntahan at dalawin ng guro sa tahanan.</p>
            '
        ]
    ],
    6 => [ // 家庭での使用言語
        'ja' => [
            'title' => '家庭での使用言語',
            'description' => '家庭で使う言語の重要性について',
            'content' => '
                <h3>1．家庭の言語の大切さ</h3>
                <p>子どもは今、保護者と何語で会話していますか？保護者の母語ですか？それとも日本語ですか？</p>
                <p>子どもにとって家庭で話す言葉はとても大切です。家庭での言語が十分育っていると、日本語もよく育つと言われています。</p>
                <p>子どもが日本語を早く覚えてほしいからといって、家庭で母語を使わず日本語ばかりを使うようにしている家庭はありませんか？保護者の日本語能力が日本人と同じレベルであれば、子どもとの会話は問題が少ないと言えます。そうではない場合、子どもとのコミュニケーションには母語（保護者が一番自由に使える言語）を使うことが大切です。</p>
                <p>弱い言語である日本語ばかりを使っていると、保護者と子どもの間で十分なコミュニケーションをとることが難しくなります。逆に、母語でしっかり育った子どもは、日本語もどんどん身につけていきます。子どもは言葉を覚えるのも早いですが、忘れるのも早いのです。母語を使う機会が減ると、母語を忘れてしまいます。母語から母語でコミュニケーションをとることを心がけましょう。</p>
                
                <h3>2．母語を忘れるとどうなるの？</h3>
                <p><strong>【会話が成り立たない！】</strong></p>
                <p>母語を忘れるうえで一番大きな問題は、保護者と会話をする言葉を失うことです。保護者と会話をする言葉を失うということはどういうことでしょう？日本語だけ話せればいいと思いますか？</p>
                <p>子どもとの会話は簡単な日本語でできるかもしれません。しかし、中学生、高校生へと進学していくにつれて、保護者の日本語能力が高いとしても、母語を使わないと十分な会話ができなくなってしまいます。進学のことや将来のことを話し合いたいのに話し合えないという事態になってしまうのです。子どもは心も不安定になります。子どもの心の安定のためにも、母語でしっかり会話をしましょう。</p>
                
                <p><strong>【母語も日本語も言語能力が低いとどうなる？】</strong></p>
                <p>小学校に入ると、学ぶ上で必要な言葉がたくさん出てきます。そしてその言葉は目に見えるものだけではなく、目に見えない概念的な内容になったり自分の意見について述べたり、学年が上がるにつれて必要とされる言語能力が高くなっていきます。</p>
                <p>母語でしっかり育った子どもは、母語で理解し日本語で表現することができます。日本語で知らない概念については、母語で理解できると理解することができます。母語でも日本語でも知らない場合については理解できません。母語と日本語の両方が弱いと、どちらでも理解できないことになります。そうすると、子どもは思考する力が育たないことになります。母語でしっかり育てると、言葉を理解し考える力が養えます。教育学習に大きな影響を与えるのです。</p>
                
                <h3>3．どうしたらいい？</h3>
                <p>「日本にいるんだから日本語だけ！いつか国に帰るから母語だけ！」ではなく、母語も日本語も大切にしましょう。</p>
                <p>保護者は母語を使うと努力しないでも自然に教育できます。母語を使って育てていくと、子どもは母語と日本語を使い分けて話せるようになっていきます。</p>
            '
        ],
        'en' => [
            'title' => 'Languages Used at Home',
            'description' => 'About the importance of languages used at home',
            'content' => '
                <h3>1. The importance of language at home</h3>
                <p>What language does the child speak to his/her father and mother now? Is it in his/her father\'s and mother\'s mother tongue? Is it Japanese? The language spoken at home is very important for the child. If the child is well brought up in the home language it is believed that he/she will also be well brought up in Japanese. Are you one of those families which ban the use of your own mother tongue at home because you want your child to be fluent in Japanese as soon as possible? If Japanese language ability of the parents are the same level as native Japanese people, it may not be a problem to communicate with the child. If it is not, it is important to use the language which parents can speak most freely (mother tongue) to communicate with your child.</p>
                <p>Young children have not been well brought up in their own mother tongue. As there are plenty of opportunities to be in touch with the Japanese language at the school, the child will be able to speak in Japanese better than your own language. Children learn languages quickly, as well as forgetting them quickly. The more opportunities the child has to be in touch with the Japanese languages, the more easily the child will forget his/her own mother tongue. Keep it in mind to communicate with your child in your mother tongue on a regular basis.</p>
                
                <h3>2. What happens if the child forgets his/her own mother tongue?</h3>
                <p><strong>[You cannot communicate!]</strong></p>
                <p>The worst problem of forgetting one\'s mother tongue is not being able to converse with one\'s parents. What would it be like to lose the language that allows one to converse with one\'s parents? Do you think it is alright to speak only in Japanese? A child may be able to understand his/her parents using simple Japanese at his/her early stage. However when the child progresses to junior high and high school, if the child speaks only in Japanese although the parents speak in their mother tongue, they cannot discuss topic like further study and the child\'s future. This may cause a situation in which the child may become emotionally unstable. For the sake of child\'s mental balance, please communicate well with the child in your mother tongue on a regular basis.</p>
                
                <p><strong>[What happen if the child\'s language abilities are low both in mother tongue and Japanese?]</strong></p>
                <p>Once the child enters the elementary school, he/she has to pick up many new vocabularies which are necessary to follow the study in the classroom. Verbal ability is important, not only to explain visible things, but also, he/she has to explain or express the things are not visible as the grade goes up. (For example: Tree → Nature → environment) If the child masters his/her mother tongue and is able to think in his/her language, it is possible to replace the term into his/her language when he/she cannot understand it in Japanese to understand the meanings. It would be very difficult for the child to understand the concept which he/she does not know in neither languages. It is very important for the child to master the mother tongue or to educate in Japanese as otherwise he/she cannot develop the ability to think. It may cause a big impact on the child\'s education.</p>
                
                <h3>3. What should we do then?</h3>
                <p>You should treat both your mother tongue and Japanese with equal importance. It should not be "only Japanese while in Japan" or "only your native language because you will go back your home country eventually". It would be better for the parents to use their most proficient language, which is their mother tongue, rather than force themselves to speak in Japanese. This in return enables the child to use both the mother tongue and Japanese in a situation demands.</p>
            '
        ],
        'zh' => [
            'title' => '关于在家里使用的语言',
            'description' => '关于在家里使用语言的重要性',
            'content' => '
                <h3>1．家庭内语言的重要性</h3>
                <p>孩子现在和家长说话的时候用什么语言呢？是你的母语呢还是日语呢？母语对孩子来说，家庭内语言的作用是非常重要的。若能妥善使用家庭内语言带来的育儿效果，日语也会有很好的育儿效果。不会么？为了让孩子早点掌握日语，而在家里不使用母语，只使用日语的家庭，是否存在呢？家长如果日语能力和日本人同等水平的话，跟孩子的交流不会有太大的问题。但如果并不是那样的话，孩子不管怎么说，日语也说不过家长的母语。父母能说得最好的语言就是母语。母语使用的机会越多，孩子掌握语言的速度就越快。孩子掌握语言的速度快，忘记语言的速度也快。为了避免母语被忘掉，请经常在家里使用母语进行交流。</p>
                
                <h3>2．忘记母语会怎么样？</h3>
                <p><strong>【无法对话！】</strong></p>
                <p>忘记母语最大的问题就是，孩子会失去跟家长交流的语言。失去跟家长交流的语言意味着什么呢？你认为只用日语和孩子说话就可以吗？孩子在小的时候，用简单的日语和家长交流可能没什么大问题。但是，到了中学、高中，即使家长日语很好，如果在家里只用日语而不使用母语的话，孩子也无法展开深入的对话。将来，孩子会迷茫、精神不安定。为了能让孩子安全、精神安定，请经常在家里用母语跟孩子交流沟通。</p>
                
                <p><strong>【母语能力和日语能力都低下的情况下会怎么样？】</strong></p>
                <p>进入小学之后，学习上需要的语言会越来越多。而且，这些语言不仅限于眼睛看到的东西，还包括看不见的东西。高年级时，需要表达一些概念性较高的内容。母语能掌握好的人，可以用母语理解这些内容，然后再用日语去表达出来。母语没有掌握好的人，就只能用日语去理解，但却不能完全理解。这对孩子来说是非常困难的。母语和日语能力都低下的孩子，最终什么都理解不了。对于孩子的学习会造成极大的影响。</p>
                
                <h3>3．那么怎么办才好呢？</h3>
                <p>因为在日本生活所以只学日语，或以后要回国所以只学母语，这样的想法是不对的。母语和日语要同样重视。家长比较容易努力用母语来教育孩子。用自己最熟悉的语言（母语）来养育孩子，孩子自然就能熟练地使用母语和日语并行展开来使用。</p>
            '
        ]
    ],
    2 => [ // 先生とのお話
        'ja' => [
            'title' => '先生とのお話',
            'description' => '先生との相談や面談について',
            'content' => '
                <p>分からないことや困ったことがあったら、先生に相談しましょう。</p>
                
                <p>小学校では、先生と保護者が直接会って、子どものこと、家庭のこと、小学校での生活のことなどを相談したり、情報交換したりする日があります。小学校に行くときはスリッパを忘れずに持って行きます。もちろん、先生とお話ししたいときは、いつでも小学校に連絡してください。</p>
                
                <h3>1．家庭訪問</h3>
                <p>先生が、各家庭に行って家で保護者とお話をします。先生が家に行く時間には必ず保護者は家にいてください。事前に都合のよい時間を聞いて時間を調整します。</p>
                <p>先生は計画を立てて、1日に何軒かまわります。そのため、1つの家庭でお話する時間は決まっています。時間を延ばすことはできません。あらかじめ、先生に相談したいことがある場合は内容をまとめておきましょう。</p>
                <p>先生は、保護者とお話するだけではなく、子どもの家庭での生活の様子を見たり、家がある場所を確認します。家の中に入ってお話をする場合や玄関でお話をする場合があります。</p>
                <p>おみやげやお茶、おかし、食事などを出す必要はありません。</p>
                <p>長期欠席や連絡がとれない場合にも家庭訪問をすることがあります。</p>
                
                <h3>2．個別懇談会／クラス懇談会</h3>
                <p>保護者が、小学校で先生とお話をします。子どものことについて相談したいことや小学校での生活のことなどを先生に聞きたいことや知りたいことを、直接先生と話をすることができる貴重な時間です。</p>
                <p>個別懇談会では、先生が保護者と1対1でお話します。お話できる時間は決まっています。事前に都合のよい時間を先生に伝えて調整します。遅れないようにしましょう。遅れると次の人の順番になってしまいます。仕事の都合で決められた時間に行けなくなってしまう場合は、必ず事前に学校に連絡して理由を説明しましょう。</p>
                <p>クラス懇談会では、クラスの保護者が集まって、先生とお話をします。これはクラスの保護者とお話できるいい機会です。積極的に参加してみましょう。</p>
            '
        ],
        'en' => [
            'title' => 'Talking to the Teacher',
            'description' => 'About consultations and meetings with teachers',
            'content' => '
                <p>If you have any concern or anything you do not understand, please talk to the teacher.</p>
                
                <p>At elementary school, there is a day when the teacher gets to meets the parents to talk and exchange information about the children, the family and the child\'s life at the elementary school. Please do not forget to bring your own slippers to the school. Nevertheless, please talk to the teacher at other times when you need to.</p>
                
                <h3>1. Home visit (kateihoumon)</h3>
                <p>The teacher conducts home visits to each family\'s home to talk to the guardians. The guardians must be at home during the time when the teacher comes visiting. A suitable time is arranged for both parties beforehand.</p>
                <p>The teacher has to follow a schedule for the day when he/she has several visits. As such, the amount of time allocated for each family is fixed. It is not possible to extend the time. Summarize the content of the things you want to talk to the teacher about in advance.</p>
                <p>The teacher does not only talk to the guardians but also observe the child in his/her own home environment, as well as confirming the location of the house. The talk can be conducted either inside the house or at the entrance of the house.</p>
                <p>It is not necessary to take out presents, drinks, snacks, food, etc.</p>
                <p>The home visits may also conducted for those with long absenteeism.</p>
                
                <h3>2. Individual meeting (kobetsu kondankai) / Class meeting (kurassu kondankai)</h3>
                <p>This is when the guardians get to talk to the teacher at the elementary school. Topics discussed include things the guardians want to find out about the child and life at school. This is an important time when information can be found out directly.</p>
                <p>During and individual meeting, the teacher has a one-to-one meeting with the guardian. The amount of time is fixed. A suitable time is arranged for both parties beforehand. Please do not be late. When you are late, it will be the next person\'s turn. If you are not able to go due to work, you have to inform the school before your appointed time.</p>
                <p>In a class meeting, the guardians of children of the class gather together to talk to the teacher. This is a good opportunity to talk to other guardians in the same class. Please participate earnestly.</p>
            '
        ],
        'zh' => [
            'title' => '与老师的谈话',
            'description' => '关于与老师的咨询和面谈',
            'content' => '
                <p>如果有不懂或感到困惑的地方，请向老师咨询。</p>
                
                <p>小学校专门有让老师和家长见面谈话的日子。谈一谈孩子的事情、家庭的事情、小学校的生活，是日和家长沟通和信息交换的机会。去小学校的时候，别忘了带拖鞋。当然，如果有想和老师谈的话，随时都可以跟小学校联系。</p>
                
                <h3>1．家庭访问</h3>
                <p>老师去各个家庭，在学生的家里和家长谈话。老师去的时候，请家长一定要在家。事先问问老师的时间，然后调整自己的时间。</p>
                <p>老师会事先做好计划，一天要去访问好几家。所以，在一个家庭谈话的时间是安排好的，不能延长时间。如果有想跟老师说的事，请提前想好。</p>
                <p>老师的访问，不光是为了和家长谈话，也为了看看孩子在家里的生活样子，确认每一个孩子的住所。有时候会到家里面去谈话，有时候只在门口说话。</p>
                <p>不需要给老师准备礼物、茶水、点心、饭菜等。</p>
                <p>即使长期缺勤或联系不上的孩子，老师有时候也会去访问的。</p>
                
                <h3>2．个别谈话／班级愿谈会</h3>
                <p>家长在小学和老师谈话。可以跟老师谈一谈关于孩子的事，问一问关于小学校生活的事或者自己不太明白的事。想知道的事，这是老师和家长见面谈话的宝贵时间。</p>
                <p>个别谈话是跟老师一对一地谈话。谈话时间是规定好的。事先沟通时间，凑出都能自己时间的日子和时间。不要迟到。如果迟到就会影响到下一个人的顺序。如果因为工作原因不能按当天定好的时间去的方法的话，一定要提前跟学校联系说明情况。</p>
                <p>班级愿谈会是一个班级的所有学生的家长都集合到一起，和老师谈话。这也是可以跟其他同学的家长谈话的机会。请积极地参加。</p>
            '
        ],
        'tl' => [
            'title' => 'Pakikipag-usap sa guro',
            'description' => 'Tungkol sa pakikipagkumustahan at pakikipagpulong sa guro',
            'content' => '
                <p>Sumangguni sa guro kung may mga katanungan o hindi maintindihan.</p>
                
                <p>May nakatakdang araw ng pag-uusap ng guro at magulang tungkol sa bata, tungkol sa pamilya at iba pang tungkol sa pamumuhay ng bata sa elementarya. Ito rin ang araw ng pagpapalitan ng impormasyon. Huwag kalilimutang magdala ng tsinelas pagpunta ng paaralan. Bukod sa araw na ito, maaari rin namang makipag-usap sa guro sa anumang oras kung may nais sabihin.</p>
                
                <h3>1. Ang Pagdalaw ng Guro sa Tahanan (Katei Houmon)</h3>
                <p>Dadalaw ang guro sa bawat tahanan o bahay ng mga bata. Siguraduhing nasa bahay ang magulang sa nakatakdang oras ng pagdalaw ng nito. Isasaayos at iaawas ng guro ang oras sa magulang kung kelan puwedeng makadalaw ito sa tahanan o bahay.</p>
                <p>Ang nakatakdang petsa at oras ay ipapaalam sa magulang bago dumalaw sa bahay. At dahil sa mga ilang bahay ang mapupuntahan ng guro sa loob ng isang araw, may takdang oras ng pamamalagi ang guro. Hindi ito puwedeng magtagal sa isang bahay, kung kayat ihanda na agad ang mga nais na itanong o sasabihin sa guro.</p>
                <p>Ang pakay ng guro ay hindi lamang para kausapin ang magulang o tagapag-alaga kundi aalamin din nito kung nasa anong sitwasyon ang pamumuhay ng bata at para malaman kung saang lugar ito nakatira. May pagkakataon na papasok ang guro sa loob ng bahay at may pagkakataon naman na sa may pintuan ng bahay lang ito makikipag-usap.</p>
                <p>Hindi na kailangang magabala sa paghahanda ng pagkain o inumin sa araw na ito.</p>
                <p>Kapag ang bata ay matagal ng hindi pumapasok sa klase, may posibilidad na puntahan at dalawin ng guro sa tahanan.</p>
                
                <h3>2. Pag-uusap ng Guro at Magulang (Kobetsu Kondankai) / Pag-uusap ng Mga Guro at Mga Magulang sa Klase (Kurasu Kondankai)</h3>
                <p>Dito ay pag-uusapan ang lagay ng bata sa elementarya. Maaaring humingi ng payo sa guro o magtanong ukol sa pamumuhay ng bata sa paaralan. Ito ay isang mahalagang oras para makapag-usap ang magulang at guro ng personal.</p>
                <p>Ang Kobetsu Kondankai ay usapan ng magulang ng bata at guro lamang. May nakatakdang haba ng oras ang usapan. Ang oras at araw ay aalamin at aayusin ng guro na ayon sa puwedeng oras ng magulang bago ito dumating sa takdang araw. Upang maiwasan ang pagkaantala sa iba pang mga magulang na darating sa araw na ito, tiyakin ang oras na nakatakda. Siguraduhin ang pagdating sa elementarya at huwag magpahuli sa nakatakdang oras. Kung hindi makakarating sa takdang oras dahil sa trabaho, mangyari lamang na tumawag sa paaralan hanggat maaga.</p>
                <p>Ang Kurasu Kondankai ay pagtitipon ng mga magulang ng mga bata sa buong klase at ito rin ang oras ng pakikipag-ugnayan nila sa guro. Maging aktibo tayo sa pagsali sa pagtitipong ito.</p>
            '
        ]
    ],
    4 => [ // 給食
        'ja' => [
            'title' => '給食',
            'description' => '学校給食について',
            'content' => '
                <p>分からないことや困ったことがあったら、先生に相談しましょう。</p>
                
                <h3>1．給食ってなに？</h3>
                <p>給食とは、小学校で食べるお昼ご飯のことです。栄養のバランスを考えて作られています。</p>
                <p>昼食の実践、食べることの大切さ、食べる時のマナーを一緒に学びます。</p>
                <p>給食の時間は決まっています。みんなで一緒に食べます。お昼の時間内に家に帰って食べることはできません。日本では、多くの保育園や中学校でも給食があります。</p>
                
                <h3>2．給食にかかるお金</h3>
                <p>給食は無料ではありません。毎月給食費を払います。</p>
                
                <h3>3．給食の味付けについて</h3>
                <p>基本的には、日本人向けの味付けになっています。初めのうちは食べ慣れないこともあります。これから毎日給食を食べます。少しずつでも食べて慣れていきましょう。</p>
                
                <h3>4．食事のマナーについて</h3>
                <p>おはしの使い方、食べる時の姿勢、お茶わんを手に持って食べるなど、日本のマナーがあります。</p>
                <p>給食の時間はマナーを守って食べていると先生がいろいろと教えてくれます。みんなで楽しく食べるためにも、マナーを守ることはとても重要です。分からないことや不思議に思うことがあれば、先生に聞いてみましょう。</p>
                
                <h3>5．宗教上食べられないものがある場合</h3>
                <p>宗教上で、食べられない食べ物がある場合には、しっかり保護者から小学校の先生に伝えてください。対応できるかどうかについては学校と相談しましょう。学校で対応できない場合には、保護者が弁当を作っていただき、その日は弁当持参を持っていくこともあります。</p>
                
                <h3>6．アレルギーなどで食べられないものがある場合</h3>
                <p>アレルギーなどで食べられない食べ物がある場合には、しっかり保護者から小学校に伝えましょう。対応について、先生と相談しましょう。</p>
                <p>小学校によっては、アレルギーなどで食べられない食材（主に卵・乳製品）を取り除いた除去食を用意できる場合もあります。病院に行って診断書をもらってから、小学校と相談しましょう。診断書には、食べられない食材や対応方法が書かれています。診断書を持って学校に相談しましょう。</p>
                <p>小学校によっては除去食が用意できない場合もあります。その場合は弁当を持っていくことも多いです。</p>
                <p><strong>（注意）</strong>除去食とは、子どもの嫌いな物を取り除くということではありません。</p>
                <p>・アナフィラキシーショックに備えて医師からエピペンを処方された場合は、必ず小学校に1本預けましょう。</p>
                <p>・献立表のほかに、より詳しく使用食材が書かれた表（学校や給食センターにあります）もあり、それをチェックするとよいでしょう。</p>
                
                <h3>7．その他</h3>
                <p><strong>(1)</strong> 給食のために各家庭で準備するものがあります。小学校によって異なりますので、確認しましょう。</p>
                <p>例）手ふき用ハンカチ、おはし、給食ナプキン、マスク、コップ、歯ブラシ（給食のあとに歯磨きをします）</p>
                <p>※必要なものは学校で確認しましょう。</p>
                <p><strong>(2)</strong> 給食の準備（みんなに配ること）から子どもが当番になってやっていきます。7～10人ぐらいの班で、白い給食着、帽子、三角巾、マスクを着けて配膳します。給食当番の人は、配膳の当番が終わったら、自宅に持ち帰って洗濯し、アイロンをかけて翌週に学校へ持っていきます。忘れると次の当番の子が困りますので忘れないようにしましょう。</p>
            '
        ],
        'en' => [
            'title' => 'School Lunch (Kyuushoku)',
            'description' => 'About school lunch',
            'content' => '
                <h3>1. What is school lunch (Kyuushoku)?</h3>
                <p>Kyuushoku (school lunch) is the food eaten at noon at the elementary school. The dishes are cooked with balanced nutrition. It is an opportunity for children to learn the importance of meal and manner as well.</p>
                <p>Lunch time is fixed by school. It is not permissible for the child to go home during lunch meal time for lunch. In Japan, most Hoikuen (nursery) and junior high schools have Kyuushoku (school lunch) as well.</p>
                
                <h3>2. Cost of lunch meal</h3>
                <p>The lunch meal is not free of charge. The meal cost is to be paid every month.</p>
                
                <h3>3. Flavors of the lunch meal</h3>
                <p>Basically it is prepared as the Japanese flavors. There may be some food that the child is not used to initially. As lunches are to be taken daily, let\'s try a little by little and get used to the flavors.</p>
                
                <h3>4. Manners at meal time</h3>
                <p>There are Japanese meal manners in Japan regarding the way the chopsticks are held, the posture while eating, holding the bowls and plates etc. During Kyuushoku time, the teachers correct the wrong manners if required. It is necessary for everyone to adhere to the right manners in order to enjoy the meal. Check with the teachers whenever there is anything you do not understand or you find baffling.</p>
                
                <h3>5. When there are some foods the child cannot consume due to religious reasons</h3>
                <p>When there are some ingredients your child cannot consume due to religious reasons, you must let the teachers at the school know. Whenever possible the types of food are inconsumable may be taken out. However, the school may not be able to comply all the times, and they seek the understanding and cooperation of the guardians, you may need to bring child\'s own lunch box for that day.</p>
                
                <h3>6. When there are ingredients the child cannot consume due to allergies</h3>
                <p>When there are some foods your child cannot consume due to allergies, you must let the teachers at the school know. Discuss with the teachers on how to tackle this.</p>
                <p>You may apply for lunch that have the ingredients that cause the allergies (mainly eggs and other dairy products) removed (allergy free diet). This allergy free diet requires instructions from the doctors with regards to the inconsumable ingredients. Get the instructions from the hospital and discuss with the school. Some elementary schools may not be able to prepare this allergy free diet. In such cases, most will bring their own lunch boxes (bentou).</p>
                <p><strong>(Note)</strong></p>
                <p>The allergy free diet does not mean taking away the type of food that the child does not like.</p>
                <p>If Epinephrine auto-injector is prescribed by doctor for anaphylaxis shock caused by food allergy, please make sure to pass one set to the school so that they keep one at school.</p>
                <p>You may request for a menu used at school or lunch center which contains more detailed information of ingredients than the regularly distributed menu. You may use it to check the ingredients.</p>
                
                <h3>7. Miscellaneous</h3>
                <p><strong>(1)</strong> Some items are required to prepare at home for the lunch. As each school is different, check with your school.</p>
                <p>(Example) hand towel, chopsticks, lunch napkin, mask, cup, toothbrush (for brushing teeth after the lunch)</p>
                <p>※Please check with the school what item needs to be cleaned daily.</p>
                <p><strong>(2)</strong> Preparation for the lunch (duties to serve meals to everyone) are done by the children themselves. Lunch duty rosters are rotated with 7 to 10 students as one group. Those on duty must wear white robe, hat, triangular bandana and masks. The student who is on duty brings the white robe and hat back home on Friday, clean, iron them and then bring them back to school on the following Monday. If you forget to bring them to school, the child who is on duty on the following week will have a problem. Do not forget to bring them.</p>
            '
        ],
        'zh' => [
            'title' => '校餐 (kyuushoku)',
            'description' => '关于学校午餐',
            'content' => '
                <h3>1．校餐是什么？</h3>
                <p>校餐是在小学吃的午饭。校餐是在考虑营养配的基础上做的。在吃午饭的时候，同时让孩子们学到吃饭的重要性、重要性，以及吃饭的礼节。</p>
                <p>校餐的时间是规定好的。大家一起吃。校餐时间不能回家吃午饭。在日本，很多保育园和中学也有校餐。</p>
                
                <h3>2．关于校餐的费用</h3>
                <p>校餐不是免费的。每个月要付校餐费。</p>
                
                <h3>3．关于校餐的调味</h3>
                <p>一般是面向日本人口味的调味。所以可能刚开始会有一些不习惯。今后每天都要吃校餐，孩子们也会慢慢地习惯。</p>
                
                <h3>4．关于吃饭的礼节</h3>
                <p>关于饭子的用法，吃饭的姿势，要拿碗吃饭，日本有自己的礼节。在学校和大家一起吃饭的时候，老师会教孩子们正确的吃饭方法。为了大家能一起快乐吃饭，遵守规矩是很重要的。如果有什么不明白或感到奇怪的地方，都可以问老师。</p>
                
                <h3>5．如果宗教信仰上有不能吃的东西</h3>
                <p>如果宗教信仰上有不能吃的东西，家长一定要清楚地告诉小学校的老师。学校方面也会尽力予以应对。如果学校不能应对的时候，家长就要自己准备便当。</p>
                
                <h3>6．如果对什么食物有过敏，不能吃的话</h3>
                <p>如果对什么食物有过敏，不能吃的话，家长一定要清楚地告诉小学校的老师。对于怎么解决，请和学校的老师商量决定。</p>
                <p>一般，请的话，小学校会为孩子准备不含过敏物（主要是鸡蛋、乳制品）的校餐（除去餐）。这种情况的话，要带着医院的诊断书。诊断书上有不能吃的食物和对应的办法。拿着诊断书去和学校商量。</p>
                <p>小学校可能无法准备除去餐的时候，家长就要准备便当。</p>
                <p><strong>（注意）</strong>除去餐不可以去掉孩子不喜欢吃的东西。</p>
                <p>・医生如果开了「エピペン」(epipen)，要把其中一个交给学校。</p>
                <p>・校餐菜单表外，还有更详细的食材使用表。请家长去看。</p>
                
                <h3>7．其他</h3>
                <p><strong>(1)</strong> 有一些校餐时用的东西，需要各个家庭准备。每个小学校可能会不太一样，请确认。</p>
                <p>例）擦手用的手帕、筷子、校餐用毛巾、口罩、水杯、牙刷（吃完校餐要刷牙）</p>
                <p>※关于需要家中自带的东西，请向老师确认。</p>
                <p><strong>(2)</strong> 校餐的准备（大家配发餐），都是由孩子们自己做的。一般是由7～10人组成一组，轮流值日。当值日的人，要穿着白衣、戴着帽子、三角巾、口罩来配餐。值日的孩子们回家后，要把衣服洗好、烫好，在下周带回学校。如果忘记带到学校，下一个值日的孩子就会有麻烦。</p>
            '
        ],
        'tl' => [
            'title' => 'Tanghalian (Kyuushoku)',
            'description' => 'Tungkol sa pagkain na inihahanda sa paaralan',
            'content' => '
                <h3>1. Ano ang Kyuushoku?</h3>
                <p>Ito ay ang pagkain sa tanghalian ng mga bata sa elementarya. Ito ay inihahanda na balanse sa nutrisyon.</p>
                <p>Itinuturo ang kasiyahan, kahalagahan at wastong asal sa pagkain.</p>
                <p>May takdang oras ng pagkain ng tanghalian. Ang lahat ay sama-samang kakain. Hindi maaaring umuwi sa bahay para kumain ng pananghalian. Sa Japan, karamihan sa nursery at Junior High School ay mayroon ding pananghalian.</p>
                
                <h3>2. Halaga ng Pananghalian</h3>
                <p>Hindi libre ang pananghalian.</p>
                <p>Ito ay buwanang binabayaran.</p>
                
                <h3>3. Panlasa ng Pananghalian</h3>
                <p>Ito ay nababase sa panlasa ng mga Hapon. Sa umpisa ay maaaring hindi makakain ng mabuti. Dahil ito ay kakanin sa araw-araw, makakasanayan din panlasa nito sa paunti-unting pagkain.</p>
                
                <h3>4. Ang Wastong Pag-uugali (asal) sa Pagkain</h3>
                <p>Sa Japan, mayroong wastong pamamaraan sa paggamit ng chopsticks, tamang pag-upo at paghawak ng pinggan.</p>
                <p>Kinakailangang sundin ang tamang paraan upang ang lahat ay maging masaya sa hapag kainan. Kung mayroong hindi maintindihan o may pagkakaiba sa sariling paniniwala, magtanong sa guro.</p>
                
                <h3>5. Kung may Pagkaing Ipinagbabawal sa Relihiyon</h3>
                <p>Kung mayroong pagkain na bawal kainin ayon sa relihiyon, ipaalam lamang ito sa guro. Kung hanggat maaari ito ay masusunod, subalit may mga hindi maiiwasang pagkakataon na dahil dito; kinakailangan ang kooperasyon ng magulang. Magdala ng sariling baong pagkain.</p>
                
                <h3>6. Mga Pagkaing Ipinagbabawal dahil sa Allergy</h3>
                <p>Kung mayroong bawal na pagkain dahil sa allergy, kinakailangang ipaalam ito nang maayos sa guro ng elementarya.</p>
                <p><strong>(Paunawa)</strong></p>
                <p>• Hindi puwedeng tanggalin ang sangkap sa pagkain sa dahilang ayaw lamang itong kainin ng bata.</p>
                <p>• Para sa na batang may malubhang allergy na tinatawag na ANAPHYLAXIS at kinakailangang gumamit ng gamot na epipen sa oras sumpungin o atakihin, siguraduhing ibigay sa elementarya ang isang set na gamot para sa biglaang pagsumpong.</p>
                <p>• Para sa karagdagang kaalaman ukol sa mga sangkap o lahok ng mga pagkain na inihahain sa elementarya, may listahang ipinamamahagi sa paaralan o kaya sa kyuushoku center (lugar o center kung saan niluluto at inihahanda ang mga pagkain para ipamahagi sa bawat paaralan). Basahin at alamin ang nilalaman nito.</p>
                
                <h3>7. Mga Iba pa</h3>
                <p><strong>(1)</strong> May mga dapat ihanda para sa tanghalian sa paaralan. Subalit may pagkakaiba sa bawat elementarya. Alamin mismo sa paaralan ng bata.</p>
                <p>(Halimbawa) Panyo o bimpong pamunas ng kamay, chopstick, place mat na tela, mask, baso (plastik cup), sipilyo (gagamitin pagkatapos kumain ng tanghalian).</p>
                <p>→ Tanungin sa paaralan kung ano ang araw-araw na iuuwi sa bahay para labahan o hugasan.</p>
                <p><strong>(2)</strong> Ang paghahanda ng pagkain. Ang mga bata ay may mga nakatakdang gawain para sa paghahain ng pagkain sa loob ng silid-aralan. Ito ay binubuo ng isang grupo (7~10 bata ang bawat grupo). At sa loob ng isang linggo ang kanilang toka, palitan (rotation). Ang mga batang nakatoka sa linggong iyon ay magsusuot ng puting damit, puting sumbrero o bandana sa ulo, mask habang sila ay naghahanda ng pagkain. Pagkatapos ng isang linggo nilang toka, iuuwi nila ang mga gamit na ito sa bahay. Labahan at plantsahin ang puting damit at puting sombrero at muling dalhin sa araw ng Lunes para sa susunod na gagamit. Huwag kalimutang dalhin para maiwasan ang pagkaabala sa susunod na batang nakatokang gumamit sa araw na iyon.</p>
            '
        ]
    ],
    5 => [ // PTA活動
        'ja' => [
            'title' => 'PTA活動',
            'description' => 'PTA活動について',
            'content' => '
                <h3>1．PTAって何？</h3>
                <p>PTAは英語の「Parent-Teacher Association」の略です。子どもたちの教育を支援するため、保護者と先生が協力し合って、さまざまな活動をしています。子どもが小学校に入学すると、保護者は自動的にPTAの会員になります。PTA会員の中から役員が選ばれます。役員を中心として、教育に関する学習活動、広報、文化・スポーツ活動、教育資金の募集活動などが、さまざまな委員会によって運営されます。PTAが主催する行事や活動は、役員が企画し、学校を通じて保護者全員に通知されます。</p>
                
                <p>PTAは自由参加に基づいて運営されています。活動に参加するために仕事を休む必要がある場合もあります。しかし、すべての活動は子どもたちの教育支援に関連しています。</p>
                
                <h3>2．どのような活動が行われていますか？</h3>
                <p>PTA活動は学校ごとに異なります。以下はその例です。</p>
                
                <p><strong>○ バザー</strong></p>
                <p>各家庭から不要品を持ち寄ります（自分にとっては不要でも、他の人にとっては価値のある物のリサイクル）。それらを販売し、収益は教育資金として使用されます。</p>
                <p>まず、各家庭から不要品を集めます。その後、各アイテムに価格タグを付けて販売の準備をします。</p>
                <p>バザー当日、あなたは販売者として働きます。バザーは人気のあるイベントで、多くの人が再利用品を買いに来ます。</p>
                
                <p><strong>○ 古紙回収</strong></p>
                <p>学校区内の各家庭から再利用可能な古紙を集めます。その後、回収業者に来てもらい、集めた古紙の量に基づいて料金を支払ってもらいます。収益は教育資金に充てられます。</p>
                <p>まず、古紙回収のために各家庭の協力を依頼します。その後、日時を通知し、当日作業を行います。多くの場合、古紙回収は週末や休日に行われます。</p>
                
                <h3>3．役員に選ばれたらどうすればよいでしょうか...</h3>
                <p>日本語が分からないという理由でPTA活動に参加しなくてもよいということはありません。外国人保護者も役員になることがあります。分からないことがあれば、他の人に助けを求めることができます。これは、普段あまり接することのない保護者や学校の先生と知り合う良い機会でもあります。また、お子さんの学校での様子をより詳しく知る機会も増えるでしょう。保護者が積極的に参加することをお勧めします。</p>
            '
        ],
        'en' => [
            'title' => 'PTA Activities',
            'description' => 'About PTA activities',
            'content' => '
                <h3>1. What is PTA?</h3>
                <p>PTA is the abbreviation for "Parent-Teacher Association". It is an association for parents and teachers to work together to support the children\'s education.</p>
                <p>When the child enters the elementary school, parents automatically become a member of PTA. Board members are selected from the PTA members. Various committees are run by board members such as education related learning activities, public relations, cultural and sports activities, educational fund-raising etc. PTA hosted events and activities are planned by the board members. These events and activities are announced through the school to all the parents.</p>
                
                <p>PTA is run on voluntary basis. You may have to take a day off from work to attend the activities. However all the activities are related to the child\'s education support.</p>
                
                <h3>2. What kind of activities are organized?</h3>
                <p>PTA activities differ with each school. The followings are examples.</p>
                
                <p><strong>○ Bazaar</strong></p>
                <p>Bring unwanted items from each household (recycle unwanted item what someone else could use) and sell them. Proceeds are used as the education fund.</p>
                <p>First of all, collect unwanted items from each household. Prepare to sell them by putting price tag for each item.</p>
                <p>On the bazaar day, you will be a vendor. Bazaar is a popular event and many people come to buy recycled items.</p>
                
                <p><strong>○ Collection of waste materials</strong></p>
                <p>Collect recyclable waste materials from each household in a school district. Then ask a collection dealer to come to pick them up and they will pay based on the amount of the collected materials. The proceed goes toward the education fund.</p>
                <p>First of all, ask for the cooperation of each household for the collection of waste materials. Then make an announcement of the date and time, and work on the day. Most of the times, the waste materials collection is held on weekends or holidays.</p>
                
                <h3>3. What to do when you are selected as a board member…</h3>
                <p>You have to participate PTA activities even though you do not understand Japanese. Foreign parents may take the role of a board member. If things occur which you do not understand, you may ask for help from others. This is a good opportunity to get to know other parents and school teachers who usually have less connections. The opportunity may increase to get to know more about how your child is doing in school. It is recommended for parents to participate diligently.</p>
            '
        ],
        'zh' => [
            'title' => 'PTA活动',
            'description' => '关于PTA活动',
            'content' => '
                <h3>1．PTA是什么？</h3>
                <p>PTA是英语的「家长和老师的交流会」（家长会）的简称。为了支援孩子们的教育，家长和老师一起合作做各种各样的活动。孩子入学小学时，其父母自然成为PTA的会员。PTA的母体是从会员中选出役员。以干部为中心，组成各种委员会。有关子女教育的学习活动、宣传活动、文化与体育活动、资金募集活动等都是由役员带头来进行。PTA主办的活动和行事由役员来企划，通过班级来向家长发送通知。</p>
                
                <p>PTA的活动都是自愿的。有时候需要家长请假来参加活动。但是，所有的活动都是为了支援孩子的教育。</p>
                
                <h3>2．具体都有那些活动呢？</h3>
                <p>活动内容每个学校都不一样。在这里给大家介绍一些活动的例子。</p>
                
                <p><strong>○跳蚤市场</strong></p>
                <p>各个家庭把不用的东西（自己用不着但对他人来说还有使用价值的东西）拿出来卖。收入作为教育资金。</p>
                <p>首先收集好各个家庭的不用品。然后一件一件作上价格，做贩卖的准备。</p>
                <p>跳蚤市场当日，作为卖方做作业。跳蚤市场很受欢迎，所以有很多人来买。</p>
                
                <p><strong>○废品回收</strong></p>
                <p>从校区内各个家庭回收可利用利用的废品，然后委托废品回收公司回收。根据回收的量，支付费用，作为教育资金。</p>
                <p>首先请求校区内的居民协助收集废品。然后通知回收日期和时间。回收当天有一些工作。很多时候都是在周末或假日的休息日时进行。</p>
                
                <h3>3．如果被选为干事，怎么办・・・</h3>
                <p>以不会日语为理由而不参加PTA的活动，是不可以的。外国家长也有可能被选为干事。如果有不懂的事情，可以请别人帮助。PTA的活动是与平时很少见面的家长和老师交流的机会，也是掌握自己孩子在学校的样子的好机会。请积极地参加。</p>
            '
        ],
        'tl' => [
            'title' => 'Ang Aktibidad ng PTA',
            'description' => 'Tungkol sa mga aktibidad ng PTA',
            'content' => '
                <h3>1. Ano ang PTA?</h3>
                <p>Ang PTA ay pinaikling salita na ang ibig sabihin ay "samahan ng mga magulang at ng guro ng paaralan". Ito ay isang lupon o grupo na sumusuporta sa ika-aayos ng pag-aaral at kapakanan ng mga bata. Sa pagpasok ng bata sa elementarya, ang magulang ay isa na ring miyembro ng PTA. Dito magkakaroon ng mga gawain o toka ang mga magulang na magtitipon sa paaralan para pag-usapan ang mga aktibidad na gagawin at ipapaalam sa iba pang mga magulang at guro. Sa pamamagitan ng ilang magulang na magiging puno sa aktibidad, isasakatuparan nila ang mga aktibidad na ito tulad ng sa sports, kultura, pagpapahayag ng mga balita ukol sa pag-aaral, panghihingi ng donasyon para sa edukasyon at iba pa.</p>
                <p>Ito ay isang boluntaryong aktibidad, walang bayad. May mga araw na kinakailangang lumiban sa trabaho para sa aktibidad na ito, subalit dapat na isa-isip na itong aktibidad ay para sa kapakanan ng mga bata at isang suporta para sa mas ikabubuting edukasyon.</p>
                
                <h3>2. Anong uri ang mga isinasagawa dito?</h3>
                <p>Ang mga aktibidad ay nababase sa bawat elementarya. Katulad halimbawa ng mga sumusunod:</p>
                
                <p><strong>○ Bazaar</strong></p>
                <p>Isang uri ng "recycle" sa pagtitinda ng mga gamit na hindi nagagamit sa bahay, mga maaayos at pinagliitang damit at iba pang mga gamit sa bahay na puwedeng ibenta at pagkakakitaan. Ang kikitain dito ay gagamitin para sa mga bagay na kakailanganin ng mga bata sa pag-aaral.</p>
                <p>Ang mga nalipong mga gamit mula sa mga kani-kanilang tahanan ay lalagyan ng presyo at ihahanda para sa araw ng bazaar.</p>
                <p>Sa araw na ito ang mga nakatokang magulang ang magtitinda at magsasagawa ng mga trabahong nakaatang para sa kanila. Ito ay popular na aktibidad na dinadagsa ng maraming tao.</p>
                
                <p><strong>○ Haihinkaishuu (Recycle)</strong></p>
                <p>Ito ay ang pagkokolekta ng mga recycle na mga gamit o mga bagay na hindi na ginagamit. Pag-naipon na ang mga ito, dadalhin sa isang kompanya para kunin at matumbasan ng halagang pera ayon sa dami nito. Ang perang malilikom dito ay gagamitin para sa pangangailangan sa pag-aaral ng mga bata.</p>
                <p>Ito ay pangunahing tulong na kakailanganin mula sa mga nasa paligid ng paaralan at sa komunidad nito. Ipapaalam dito ang oras at araw kung kelan ito gaganapin. Kadalasang isinasagawa ito sa araw ng Sabado o Linggo na walang pasok.</p>
                
                <h3>3. Ano ang gagawin kung maatasang maging toka sa PTA?</h3>
                <p>Hindi ibig sabihin na dahil sa hindi makaintindi at makapagsalita ng Hapon ay hindi na kailangang tumulong o makisalamuha sa mga aktibidad nito. May mga banyaga ring nagiging pinuno ng PTA sa ibang mga pagkakataon. Ang mga guro at ibang mga magulang ay nakahandang tumulong sa mga bagay na hindi nito alam. Ito rin ay isang pagkakataon para magkaroon ng komunikasyon sa ibang mga magulang at guro. Dito maraming mapupulot o matututunang mga bagay tungkol sa mga kalagayan ng mga bata sa pag-aaral sa elementarya. Maging aktibo sa pagsali sa mga gawaing ito.</p>
            '
        ]
    ],
    6 => [ // 家庭での使用言語
        'ja' => [
            'title' => '家庭での使用言語',
            'description' => '家庭で使う言語の重要性について',
            'content' => '
                <h3>1．家庭の言語の大切さ</h3>
                <p>子どもは今、保護者と何語で会話していますか？保護者の母語ですか？それとも日本語ですか？</p>
                <p>子どもにとって家庭で話す言葉はとても大切です。家庭での言語が十分育っていると、日本語もよく育つと言われています。</p>
                <p>子どもが日本語を早く覚えてほしいからといって、家庭で母語を使わず日本語ばかりを使うようにしている家庭はありませんか？保護者の日本語能力が日本人と同じレベルであれば、子どもとの会話は問題が少ないと言えます。そうではない場合、子どもとのコミュニケーションには母語（保護者が一番自由に使える言語）を使うことが大切です。</p>
                <p>弱い言語である日本語ばかりを使っていると、保護者と子どもの間で十分なコミュニケーションをとることが難しくなります。逆に、母語でしっかり育った子どもは、日本語もどんどん身につけていきます。子どもは言葉を覚えるのも早いですが、忘れるのも早いのです。母語を使う機会が減ると、母語を忘れてしまいます。母語から母語でコミュニケーションをとることを心がけましょう。</p>
                
                <h3>2．母語を忘れるとどうなるの？</h3>
                <p><strong>【会話が成り立たない！】</strong></p>
                <p>母語を忘れるうえで一番大きな問題は、保護者と会話をする言葉を失うことです。保護者と会話をする言葉を失うということはどういうことでしょう？日本語だけ話せればいいと思いますか？</p>
                <p>子どもとの会話は簡単な日本語でできるかもしれません。しかし、中学生、高校生へと進学していくにつれて、保護者の日本語能力が高いとしても、母語を使わないと十分な会話ができなくなってしまいます。進学のことや将来のことを話し合いたいのに話し合えないという事態になってしまうのです。子どもは心も不安定になります。子どもの心の安定のためにも、母語でしっかり会話をしましょう。</p>
                
                <p><strong>【母語も日本語も言語能力が低いとどうなる？】</strong></p>
                <p>小学校に入ると、学ぶ上で必要な言葉がたくさん出てきます。そしてその言葉は目に見えるものだけではなく、目に見えない概念的な内容になったり自分の意見について述べたり、学年が上がるにつれて必要とされる言語能力が高くなっていきます。</p>
                <p>母語でしっかり育った子どもは、母語で理解し日本語で表現することができます。日本語で知らない概念については、母語で理解できると理解することができます。母語でも日本語でも知らない場合については理解できません。母語と日本語の両方が弱いと、どちらでも理解できないことになります。そうすると、子どもは思考する力が育たないことになります。母語でしっかり育てると、言葉を理解し考える力が養えます。教育学習に大きな影響を与えるのです。</p>
                
                <h3>3．どうしたらいい？</h3>
                <p>「日本にいるんだから日本語だけ！いつか国に帰るから母語だけ！」ではなく、母語も日本語も大切にしましょう。</p>
                <p>保護者は母語を使うと努力しないでも自然に教育できます。母語を使って育てていくと、子どもは母語と日本語を使い分けて話せるようになっていきます。</p>
            '
        ],
        'en' => [
            'title' => 'Languages Used at Home',
            'description' => 'About the importance of languages used at home',
            'content' => '
                <h3>1. The importance of language at home</h3>
                <p>What language does the child speak to his/her father and mother now? Is it in his/her father\'s and mother\'s mother tongue? Is it Japanese? The language spoken at home is very important for the child. If the child is well brought up in the home language it is believed that he/she will also be well brought up in Japanese. Are you one of those families which ban the use of your own mother tongue at home because you want your child to be fluent in Japanese as soon as possible? If Japanese language ability of the parents are the same level as native Japanese people, it may not be a problem to communicate with the child. If it is not, it is important to use the language which parents can speak most freely (mother tongue) to communicate with your child.</p>
                <p>Young children have not been well brought up in their own mother tongue. As there are plenty of opportunities to be in touch with the Japanese language at the school, the child will be able to speak in Japanese better than your own language. Children learn languages quickly, as well as forgetting them quickly. The more opportunities the child has to be in touch with the Japanese languages, the more easily the child will forget his/her own mother tongue. Keep it in mind to communicate with your child in your mother tongue on a regular basis.</p>
                
                <h3>2. What happens if the child forgets his/her own mother tongue?</h3>
                <p><strong>[You cannot communicate!]</strong></p>
                <p>The worst problem of forgetting one\'s mother tongue is not being able to converse with one\'s parents. What would it be like to lose the language that allows one to converse with one\'s parents? Do you think it is alright to speak only in Japanese? A child may be able to understand his/her parents using simple Japanese at his/her early stage. However when the child progresses to junior high and high school, if the child speaks only in Japanese although the parents speak in their mother tongue, they cannot discuss topic like further study and the child\'s future. This may cause a situation in which the child may become emotionally unstable. For the sake of child\'s mental balance, please communicate well with the child in your mother tongue on a regular basis.</p>
                
                <p><strong>[What happen if the child\'s language abilities are low both in mother tongue and Japanese?]</strong></p>
                <p>Once the child enters the elementary school, he/she has to pick up many new vocabularies which are necessary to follow the study in the classroom. Verbal ability is important, not only to explain visible things, but also, he/she has to explain or express the things are not visible as the grade goes up. (For example: Tree → Nature → environment) If the child masters his/her mother tongue and is able to think in his/her language, it is possible to replace the term into his/her language when he/she cannot understand it in Japanese to understand the meanings. It would be very difficult for the child to understand the concept which he/she does not know in neither languages. It is very important for the child to master the mother tongue or to educate in Japanese as otherwise he/she cannot develop the ability to think. It may cause a big impact on the child\'s education.</p>
                
                <h3>3. What should we do then?</h3>
                <p>You should treat both your mother tongue and Japanese with equal importance. It should not be "only Japanese while in Japan" or "only your native language because you will go back your home country eventually". It would be better for the parents to use their most proficient language, which is their mother tongue, rather than force themselves to speak in Japanese. This in return enables the child to use both the mother tongue and Japanese in a situation demands.</p>
            '
        ],
        'zh' => [
            'title' => '关于在家里使用的语言',
            'description' => '关于在家里使用语言的重要性',
            'content' => '
                <h3>1．家庭内语言的重要性</h3>
                <p>孩子现在和家长说话的时候用什么语言呢？是你的母语呢还是日语呢？母语对孩子来说，家庭内语言的作用是非常重要的。若能妥善使用家庭内语言带来的育儿效果，日语也会有很好的育儿效果。不会么？为了让孩子早点掌握日语，而在家里不使用母语，只使用日语的家庭，是否存在呢？家长如果日语能力和日本人同等水平的话，跟孩子的交流不会有太大的问题。但如果并不是那样的话，孩子不管怎么说，日语也说不过家长的母语。父母能说得最好的语言就是母语。母语使用的机会越多，孩子掌握语言的速度就越快。孩子掌握语言的速度快，忘记语言的速度也快。为了避免母语被忘掉，请经常在家里使用母语进行交流。</p>
                
                <h3>2．忘记母语会怎么样？</h3>
                <p><strong>【无法对话！】</strong></p>
                <p>忘记母语最大的问题就是，孩子会失去跟家长交流的语言。失去跟家长交流的语言意味着什么呢？你认为只用日语和孩子说话就可以吗？孩子在小的时候，用简单的日语和家长交流可能没什么大问题。但是，到了中学、高中，即使家长日语很好，如果在家里只用日语而不使用母语的话，孩子也无法展开深入的对话。将来，孩子会迷茫、精神不安定。为了能让孩子安全、精神安定，请经常在家里用母语跟孩子交流沟通。</p>
                
                <p><strong>【母语能力和日语能力都低下的情况下会怎么样？】</strong></p>
                <p>进入小学之后，学习上需要的语言会越来越多。而且，这些语言不仅限于眼睛看到的东西，还包括看不见的东西。高年级时，需要表达一些概念性较高的内容。母语能掌握好的人，可以用母语理解这些内容，然后再用日语去表达出来。母语没有掌握好的人，就只能用日语去理解，但却不能完全理解。这对孩子来说是非常困难的。母语和日语能力都低下的孩子，最终什么都理解不了。对于孩子的学习会造成极大的影响。</p>
                
                <h3>3．那么怎么办才好呢？</h3>
                <p>因为在日本生活所以只学日语，或以后要回国所以只学母语，这样的想法是不对的。母语和日语要同样重视。家长比较容易努力用母语来教育孩子。用自己最熟悉的语言（母语）来养育孩子，孩子自然就能熟练地使用母语和日语并行展开来使用。</p>
            '
        ],
        'tl' => [
            'title' => 'Mga Salitang Ginagamit sa Loob ng Bahay',
            'description' => 'Tungkol sa kahalagahan ng wikang ginagamit sa tahanan',
            'content' => '
                <h3>1. Kahalagahan ng Pananalita sa Tahanan</h3>
                <p>Ano ang ginagamit na salita kapag nakikipag-usap sa magulang o tagapag-alaga? Ginagamit ba ang wika ng sariling bansa o Hapon? Pinakamahalaga sa bata ang ginagamit na salita sa bahay. Kapag nahubog nang sapat ang bata sa wikang ginagamit sa tahanan, sinasabing mas madali ang paghubog ng pag-aaral sa wikang Hapon. Upang mabilis matuto ng Hapon ang bata, kailangan bang ito rin ang wika na gagamitin sa bahay at hindi na dapat gamitin ang sariling wika? Kung ang magulang o tagapag-alaga ng bata ay malugod na nakakaunawa at nakapagsasalita ng wikang Hapon, ito ay walang problema. Subalit kung ang magulang ay hindi gaanong nakakapagsalita, ito ay magiging suliranin sa bata para unawain ang mga sinasabi ng magulang. Mas mainam gamitin ang salitang malayang ginagamit ng magulang upang maipaliwanag nito ng maayos sa bata ang kanilang mga sinasabi at para maunawaang lubos ang ibig sabihin ng magulang (mutual understanding).</p>
                <p>Ang bata ay hindi pa gaanong nahuhubog maging sa sariling wika. Pagpasok ng elementarya madaling masasanay at matututong magsalita ng Hapon sa pang-araw-araw na pakikisalamuha sa kanilang kapwa. At dahil dito, mas magiging mahusay ang pagsasalita nila sa wikang Hapon. Kung mabilis matutong magsalita ang bata, madali at mabilis din itong makalimot. At dahil sa mas may maraming oras sa pagsasalita ng Hapon, may posibilidad na makalimutan nito ang sariling wika. Para maiwasan ang ganitong pangyayari, kinakailangan ang patuloy na komunikasyon ng magulang sa bata na gamit ang sariling wika.</p>
                
                <h3>2. Ano ang mangyayari kapag nakalimutan ang sariling wika?</h3>
                <p><strong>【Hindi sapat ang komunikasyon】</strong></p>
                <p>Isa sa pinakamadaling suliranin ang makalimutan ng bata ang sariling wika. Ano sa palagay ninyo ang tungkol dito? Inaakala ba ninyong Hapon lang ang dapat gamiting wika ng bata? Habang maliit pa ang bata, nagkakaintindihan pa sila ng magulang sa pamamagitan ng mga madaling salita na alam ng magulang sa wikang Hapon. Subalit kapag ang bata ay sumapit sa Junior High School at pataas, kahit na kausapin ng magulang sa sariling wika ang anak, kalimitang isinasagot nito ay sa Hapon na. Unti-unting nakakalimutan nito ang sariling wika, hinggil pa dito mahihirapan nang makipag-usap tungkol sa mga pag-aaral o tungkol sa kinabukasan ng bata. Dahil sa mga balakid ng komunikasyong ito, nagiging magulo ang isip ng bata at hindi malaman nito ang gagawin. Pati na rin ang damdamin ng bata ay apektado sa ganitong sitwasyon. Para magkaroon ng kapanatagan sa isip at damdamin, higit na nararapat ang patuloy na pakikipag-usap o komunikasyon sa sariling wika.</p>
                
                <p><strong>【Ano ang mangyayari kung parehong mahina sa sariling wika at sa Hapon?】</strong></p>
                <p>Sa pag-aaral ng bata sa elementarya, maraming mga salitang Hapon na matututunan dito at hindi lang mga salita kung hindi mga bagay na mahirap ipaliwanag sa isang salita. Kinakailangang pag-isipang mabuti kung anong ibig sabihin ng salitang ito katulad ng halimbawa; puno --- likas yaman --- kapaligiran. Kung ang bata ay lubusang nakakaintindi sa sariling wika, hindi ito mahihirapang mag-isip at umunawa, subalit kung ang bata ay hindi sapat na nakakaunawa sa sariling wika, mahihirapan itong umunawa kung ano ang ibig sabihin. Hindi rin nito kayang umunawa sa Hapon. Kung kayat ang mangyayari ay parehong mahihirapan sa pagsasalita at pag-iisip ang bata sa anumang wika. Sa dahilang ito, magiging isang malaking impluwensiya at balakid para sa pag-aaral at sa kinabukasan ng bata.</p>
                
                <h3>3. Ano ang dapat gawin?</h3>
                <p>Hindi ibig sabihin na dahil dito sa Japan nakatira ay Hapon ang dapat gamiting salita o hindi naman ibig sabihin na dahil sa uuwi babalik naman sa sariling bayan ay sariling wika lang ang gagamitin. Pangalagaan pareho ang sariling salita at Hapon. Kung ano ang wika na madaling sabihin o gamitin ng magulang, ito ang gamiting uri ng komunikasyon para sa maayos, malayang pagpapaliwanag at para maipahayag ang mga dapat ipabatid sa bata. Upang matutong magsalita ang bata na gamit ang dalawang linguwahe o wika sapagkat alam nito kung paano gagamitin.</p>
            '
        ]
    ]
];

$stories = [
    1 => [
        'title' => '日本の学校について',
        'description' => '日本の学校の基本的なこと',
        'content' => '
            <p>日本の教育制度は「6-3-3-4制」と呼ばれています。</p>
            <p>小学校が6年間、中学校が3年間、高校が3年間、大学が4年間なので、「6-3-3-4」と呼ばれています。</p>
            
            <p>小学校と中学校は「義務教育」です。</p>
            <p>高校を卒業した後は、大学、短期大学、または専門学校や職業訓練校などに進学する学生もいます。</p>
            
            <p>日本の学年は4月に始まり、翌年の3月に終わります。</p>
            <p>小学校、中学校、高校では、通常1年を2学期または3学期に分けています。</p>
            <p>学期とは授業が行われる期間のことで、この学期の間には、長い休みがあります。</p>
            
            <div style="margin: 20px 0; text-align: center;">
                <div style="display: inline-block; background: white; border: 2px solid #333; padding: 15px; max-width: 600px; width: 100%;">
                    <div style="display: flex; align-items: center; margin-bottom: 10px;">
                        <div style="flex: 8.5; background: #ffeb3b; color: #333; text-align: center; padding: 5px 15px; font-weight: bold; border: 1px solid #333;">義務教育</div>
                        <div style="flex: 7.5;"></div>
                    </div>
                    <div style="display: flex; align-items: stretch; margin-bottom: 10px;">
                        <div style="flex: 6; background: #ff5050; color: white; text-align: center; padding: 15px; font-weight: bold; border: 1px solid #333;">小学校</div>
                        <div style="flex: 3; background: #ff8080; color: white; text-align: center; padding: 15px; font-weight: bold; border: 1px solid #333;">中学校</div>
                        <div style="flex: 3; background: #ffb3b3; color: #333; text-align: center; padding: 15px; font-weight: bold; border: 1px solid #333;">高等学校</div>
                        <div style="flex: 4; background: #ffe6e6; color: #333; text-align: center; padding: 15px; font-weight: bold; border: 1px solid #333;">大学</div>
                    </div>
                    <div style="display: flex; align-items: center;">
                        <div style="flex: 6; text-align: center; font-weight: bold;">6年</div>
                        <div style="flex: 3; text-align: center; font-weight: bold;">3年</div>
                        <div style="flex: 3; text-align: center; font-weight: bold;">3年</div>
                        <div style="flex: 4; text-align: center; font-weight: bold;">4年</div>
                    </div>
                </div>
            </div>
        ',
        'icon' => 'school2.png'
    ],
    2 => [
        'title' => '先生とのお話',
        'description' => '先生との相談や面談について',
        'content' => '
            <p>分からないことや困ったことがあったら、先生に相談しましょう。</p>
            
            <p>小学校では、先生と保護者が直接会って、子どものこと、家庭のこと、小学校での生活のことなどを相談したり、情報交換したりする日があります。小学校に行くときはスリッパを忘れずに持って行きます。もちろん、先生とお話ししたいときは、いつでも小学校に連絡してください。</p>
            
            <h3>1．家庭訪問</h3>
            <p>先生が、各家庭に行って家で保護者とお話をします。先生が家に行く時間には必ず保護者は家にいてください。事前に都合のよい時間を聞いて時間を調整します。</p>
            <p>先生は計画を立てて、1日に何軒かまわります。そのため、1つの家庭でお話する時間は決まっています。時間を延ばすことはできません。あらかじめ、先生に相談したいことがある場合は内容をまとめておきましょう。</p>
            <p>先生は、保護者とお話するだけではなく、子どもの家庭での生活の様子を見たり、家がある場所を確認します。家の中に入ってお話をする場合や玄関でお話をする場合があります。</p>
            <p>おみやげやお茶、おかし、食事などを出す必要はありません。</p>
            <p>長期欠席や連絡がとれない場合にも家庭訪問をすることがあります。</p>
            
            <h3>2．個別懇談会／クラス懇談会</h3>
            <p>保護者が、小学校で先生とお話をします。子どものことについて相談したいことや小学校での生活のことなどを先生に聞きたいことや知りたいことを、直接先生と話をすることができる貴重な時間です。</p>
            <p>個別懇談会では、先生が保護者と1対1でお話します。お話できる時間は決まっています。事前に都合のよい時間を先生に伝えて調整します。遅れないようにしましょう。遅れると次の人の順番になってしまいます。仕事の都合で決められた時間に行けなくなってしまう場合は、必ず事前に学校に連絡して理由を説明しましょう。</p>
            <p>クラス懇談会では、クラスの保護者が集まって、先生とお話をします。これはクラスの保護者とお話できるいい機会です。積極的に参加してみましょう。</p>
        ',
        'icon' => 'hogosyakai.png'
    ],
    3 => [
        'title' => '家庭訪問',
        'description' => '先生が家庭を訪問する制度について',
        'content' => '
            <p>家庭訪問は、担任の先生が家を訪問し保護者と話をします。事前に、担任の先生から訪問予定の連絡が来るので、大丈夫だったら、大丈夫と返事をします。その日に別な予定があったら、担任の先生に変更のお願いの連絡をします。目的は、家庭環境の把握や子供の特徴の共有と理解、保護者の子育ての悩み相談など、となります。</p>
        ',
        'icon' => 'kateihoumon.png'
    ],
    4 => [
        'title' => '給食',
        'description' => '学校給食について',
        'content' => '
            <p>分からないことや困ったことがあったら、先生に相談しましょう。</p>
            
            <h3>1．給食ってなに？</h3>
            <p>給食とは、小学校で食べるお昼ご飯のことです。栄養のバランスを考えて作られています。</p>
            <p>昼食の実践、食べることの大切さ、食べる時のマナーを一緒に学びます。</p>
            <p>給食の時間は決まっています。みんなで一緒に食べます。お昼の時間内に家に帰って食べることはできません。日本では、多くの保育園や中学校でも給食があります。</p>
            
            <h3>2．給食にかかるお金</h3>
            <p>給食は無料ではありません。毎月給食費を払います。</p>
            
            <h3>3．給食の味付けについて</h3>
            <p>基本的には、日本人向けの味付けになっています。初めのうちは食べ慣れないこともあります。これから毎日給食を食べます。少しずつでも食べて慣れていきましょう。</p>
            
            <h3>4．食事のマナーについて</h3>
            <p>おはしの使い方、食べる時の姿勢、お茶わんを手に持って食べるなど、日本のマナーがあります。</p>
            <p>給食の時間はマナーを守って食べていると先生がいろいろと教えてくれます。みんなで楽しく食べるためにも、マナーを守ることはとても重要です。分からないことや不思議に思うことがあれば、先生に聞いてみましょう。</p>
            
            <h3>5．宗教上食べられないものがある場合</h3>
            <p>宗教上で、食べられない食べ物がある場合には、しっかり保護者から小学校の先生に伝えてください。対応できるかどうかについては学校と相談しましょう。学校で対応できない場合には、保護者が弁当を作っていただき、その日は弁当持参を持っていくこともあります。</p>
            
            <h3>6．アレルギーなどで食べられないものがある場合</h3>
            <p>アレルギーなどで食べられない食べ物がある場合には、しっかり保護者から小学校に伝えましょう。対応について、先生と相談しましょう。</p>
            <p>小学校によっては、アレルギーなどで食べられない食材（主に卵・乳製品）を取り除いた除去食を用意できる場合もあります。病院に行って診断書をもらってから、小学校と相談しましょう。診断書には、食べられない食材や対応方法が書かれています。診断書を持って学校に相談しましょう。</p>
            <p>小学校によっては除去食が用意できない場合もあります。その場合は弁当を持っていくことも多いです。</p>
            <p><strong>（注意）</strong>除去食とは、子どもの嫌いな物を取り除くということではありません。</p>
            <p>・アナフィラキシーショックに備えて医師からエピペンを処方された場合は、必ず小学校に1本預けましょう。</p>
            <p>・献立表のほかに、より詳しく使用食材が書かれた表（学校や給食センターにあります）もあり、それをチェックするとよいでしょう。</p>
            
            <h3>7．その他</h3>
            <p><strong>(1)</strong> 給食のために各家庭で準備するものがあります。小学校によって異なりますので、確認しましょう。</p>
            <p>例）手ふき用ハンカチ、おはし、給食ナプキン、マスク、コップ、歯ブラシ（給食のあとに歯磨きをします）</p>
            <p>※必要なものは学校で確認しましょう。</p>
            <p><strong>(2)</strong> 給食の準備（みんなに配ること）から子どもが当番になってやっていきます。7～10人ぐらいの班で、白い給食着、帽子、三角巾、マスクを着けて配膳します。給食当番の人は、配膳の当番が終わったら、自宅に持ち帰って洗濯し、アイロンをかけて翌週に学校へ持っていきます。忘れると次の当番の子が困りますので忘れないようにしましょう。</p>
        ',
        'icon' => 'kyusyoku.png'
    ],
    5 => [
        'title' => 'PTA活動',
        'description' => 'PTA活動について',
        'content' => '
            <h3>1．PTAって何？</h3>
            <p>PTAは英語の「Parent-Teacher Association」の略です。子どもたちの教育を支援するため、保護者と先生が協力し合って、さまざまな活動をしています。子どもが小学校に入学すると、保護者は自動的にPTAの会員になります。PTA会員の中から役員が選ばれます。役員を中心として、教育に関する学習活動、広報、文化・スポーツ活動、教育資金の募集活動などが、さまざまな委員会によって運営されます。PTAが主催する行事や活動は、役員が企画し、学校を通じて保護者全員に通知されます。</p>
            
            <p>PTAは自由参加に基づいて運営されています。活動に参加するために仕事を休む必要がある場合もあります。しかし、すべての活動は子どもたちの教育支援に関連しています。</p>
            
            <h3>2．どのような活動が行われていますか？</h3>
            <p>PTA活動は学校ごとに異なります。以下はその例です。</p>
            
            <p><strong>○ バザー</strong></p>
            <p>各家庭から不要品を持ち寄ります（自分にとっては不要でも、他の人にとっては価値のある物のリサイクル）。それらを販売し、収益は教育資金として使用されます。</p>
            <p>まず、各家庭から不要品を集めます。その後、各アイテムに価格タグを付けて販売の準備をします。</p>
            <p>バザー当日、あなたは販売者として働きます。バザーは人気のあるイベントで、多くの人が再利用品を買いに来ます。</p>
            
            <p><strong>○ 古紙回収</strong></p>
            <p>学校区内の各家庭から再利用可能な古紙を集めます。その後、回収業者に来てもらい、集めた古紙の量に基づいて料金を支払ってもらいます。収益は教育資金に充てられます。</p>
            <p>まず、古紙回収のために各家庭の協力を依頼します。その後、日時を通知し、当日作業を行います。多くの場合、古紙回収は週末や休日に行われます。</p>
            
            <h3>3．役員に選ばれたらどうすればよいでしょうか...</h3>
            <p>日本語が分からないという理由でPTA活動に参加しなくてもよいということはありません。外国人保護者も役員になることがあります。分からないことがあれば、他の人に助けを求めることができます。これは、普段あまり接することのない保護者や学校の先生と知り合う良い機会でもあります。また、お子さんの学校での様子をより詳しく知る機会も増えるでしょう。保護者が積極的に参加することをお勧めします。</p>
        ',
        'icon' => 'pta.png'
    ],
    6 => [
        'title' => '家庭での使用言語',
        'description' => '家庭で使う言語の重要性について',
        'content' => '
            <h3>1．家庭の言語の大切さ</h3>
            <p>子どもは今、保護者と何語で会話していますか？保護者の母語ですか？それとも日本語ですか？</p>
            <p>子どもにとって家庭で話す言葉はとても大切です。家庭での言語が十分育っていると、日本語もよく育つと言われています。</p>
            <p>子どもが日本語を早く覚えてほしいからといって、家庭で母語を使わず日本語ばかりを使うようにしている家庭はありませんか？保護者の日本語能力が日本人と同じレベルであれば、子どもとの会話は問題が少ないと言えます。そうではない場合、子どもとのコミュニケーションには母語（保護者が一番自由に使える言語）を使うことが大切です。</p>
            <p>弱い言語である日本語ばかりを使っていると、保護者と子どもの間で十分なコミュニケーションをとることが難しくなります。逆に、母語でしっかり育った子どもは、日本語もどんどん身につけていきます。子どもは言葉を覚えるのも早いですが、忘れるのも早いのです。母語を使う機会が減ると、母語を忘れてしまいます。母語から母語でコミュニケーションをとることを心がけましょう。</p>
            
            <h3>2．母語を忘れるとどうなるの？</h3>
            <p><strong>【会話が成り立たない！】</strong></p>
            <p>母語を忘れるうえで一番大きな問題は、保護者と会話をする言葉を失うことです。保護者と会話をする言葉を失うということはどういうことでしょう？日本語だけ話せればいいと思いますか？</p>
            <p>子どもとの会話は簡単な日本語でできるかもしれません。しかし、中学生、高校生へと進学していくにつれて、保護者の日本語能力が高いとしても、母語を使わないと十分な会話ができなくなってしまいます。進学のことや将来のことを話し合いたいのに話し合えないという事態になってしまうのです。子どもは心も不安定になります。子どもの心の安定のためにも、母語でしっかり会話をしましょう。</p>
            
            <p><strong>【母語も日本語も言語能力が低いとどうなる？】</strong></p>
            <p>小学校に入ると、学ぶ上で必要な言葉がたくさん出てきます。そしてその言葉は目に見えるものだけではなく、目に見えない概念的な内容になったり自分の意見について述べたり、学年が上がるにつれて必要とされる言語能力が高くなっていきます。</p>
            <p>母語でしっかり育った子どもは、母語で理解し日本語で表現することができます。日本語で知らない概念については、母語で理解できると理解することができます。母語でも日本語でも知らない場合については理解できません。母語と日本語の両方が弱いと、どちらでも理解できないことになります。そうすると、子どもは思考する力が育たないことになります。母語でしっかり育てると、言葉を理解し考える力が養えます。教育学習に大きな影響を与えるのです。</p>
            
            <h3>3．どうしたらいい？</h3>
            <p>「日本にいるんだから日本語だけ！いつか国に帰るから母語だけ！」ではなく、母語も日本語も大切にしましょう。</p>
            <p>保護者は母語を使うと努力しないでも自然に教育できます。母語を使って育てていくと、子どもは母語と日本語を使い分けて話せるようになっていきます。</p>
        ',
        'icon' => 'kateigengo.png'
    ]
];

$additional_css = '<style>
/* 背景画像設定 */
body {
    background-image: url("../assets/images/bg_top.png"), url("../assets/images/bg_bottom.png");
    background-position: center top, center bottom;
    background-repeat: no-repeat, no-repeat;
    background-size: 100% auto, 100% auto;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Noto Sans CJK SC", "Noto Sans CJK TC", "Noto Sans CJK JP", "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", "Helvetica Neue", Arial, sans-serif;
}

.stories-container {
    max-width: 1200px;
    width: 100%;
    margin: 0 auto;
    padding: 20px;
}

.stories-header {
    text-align: center;
    margin-bottom: 40px;
    padding: 30px;
    background: var(--card-background);
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    position: relative;
}


.stories-header h1 {
    color: var(--primary-dark);
    margin-bottom: 10px;
    font-size: 2.5rem;
}

.stories-header p {
    color: #666;
    font-size: 1.1rem;
}

.stories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.story-card {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    padding: 25px;
    cursor: pointer;
    transition: all 0.3s ease;
    border-top: 4px solid var(--primary-color);
    min-height: 180px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.story-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.story-icon {
    margin-bottom: 15px !important;
    display: block !important;
    font-size: inherit !important;
}

.story-icon img {
    width: 64px !important;
    height: 64px !important;
    object-fit: contain !important;
    display: block !important;
}

.story-title {
    color: var(--primary-dark);
    font-size: 1.4rem;
    font-weight: bold;
    margin-bottom: 10px;
    margin-left: 0;
    padding-left: 0;
}

.story-description {
    color: #666;
    font-size: 1rem;
    line-height: 1.6;
    margin-left: 0;
    padding-left: 0;
}

.story-modal {
    display: none;
    position: fixed;
    z-index: 2147483647 !important;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5);
    padding: 20px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.story-modal.show {
    opacity: 1;
}

.modal-content {
    background-color: white;
    margin: 20px auto 2% auto !important;
    padding: 30px;
    border: none;
    border-radius: 15px;
    width: 90%;
    max-width: 800px;
    max-height: calc(100vh - 60px);
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    position: relative;
    transform: translateY(-20px);
    transition: transform 0.3s ease;
}

.story-modal.show .modal-content {
    transform: translateY(0);
}

.close {
    position: absolute;
    right: 20px;
    top: 20px;
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.close:hover,
.close:focus {
    color: #000;
    background: #f0f0f0;
    text-decoration: none;
}

.modal-title {
    color: var(--primary-dark);
    font-size: 2rem;
    margin-bottom: 20px;
    padding-right: 50px;
}

.modal-body {
    line-height: 1.8;
    color: #333;
}

.modal-body h3 {
    color: var(--primary-color);
    margin-top: 25px;
    margin-bottom: 15px;
    font-size: 1.3rem;
}

.modal-body p {
    margin-bottom: 15px;
}

.back-btn {
    display: inline-block;
    background: var(--primary-color);
    color: white;
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: bold;
    margin-top: 30px;
    transition: all 0.3s ease;
    text-align: center;
}

.back-btn:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

@media (max-width: 768px) {
    .stories-container {
        padding: 15px;
    }
    
    .stories-header h1 {
        font-size: 2rem;
    }
    
    
    .stories-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .story-modal {
        z-index: 2147483647 !important;
    }
    
    .modal-content {
        width: 95% !important;
        padding: 20px !important;
        margin: 20px auto 5% auto !important;
        max-height: calc(100vh - 40px) !important;
        transform: translateY(-20px) !important;
    }
    
    .story-modal.show .modal-content {
        transform: translateY(0) !important;
    }
    
    .modal-title {
        font-size: 1.5rem;
    }
}

/* 教育制度図のレスポンシブスタイル */
.education-system-diagram {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
}

.education-stage {
    background: #e3f2fd;
    border-radius: 8px;
    padding: 15px;
    min-width: 120px;
    text-align: center;
    flex: 1;
    min-width: 140px;
}

.education-stage:nth-child(2) {
    background: #e8f5e8;
}

.education-stage:nth-child(4) {
    background: #fff3e0;
}

.education-stage:nth-child(6) {
    background: #fce4ec;
}

.education-arrow {
    font-size: 20px;
    color: #666;
    margin: 0 5px;
}

@media (max-width: 768px) {
    .education-system-diagram {
        flex-direction: column;
        gap: 15px;
    }
    
    .education-arrow {
        transform: rotate(90deg);
        font-size: 24px;
    }
    
    .education-stage {
        width: 100%;
        max-width: 250px;
        margin: 0 auto;
    }
}
</style>';

require_once '../includes/header.php';
?>

<div class="stories-container">
    <div class="stories-header">
        <h1 id="mainTitle" data-ja="がっこうのこと" data-en="Life at School" data-zh="学校的故事" data-tl="Buhay sa Paaralan">がっこうのこと</h1>
        <p id="mainSubtitle" data-ja="日本の学校生活について知っておきたいことをまとめました" data-en="A Summary of School Life in Japan" data-zh="整理了日本学校生活相关信息" data-tl="Buod ng Buhay sa Paaralan sa Japan">日本の学校生活について知っておきたいことをまとめました</p>
    </div>

    <div class="stories-grid">
        <?php foreach ($stories as $id => $story): ?>
            <div class="story-card" onclick="openStoryModal(<?= $id ?>)" data-story-id="<?= $id ?>">
                <span class="story-icon">
                    <?php if (!empty($story['icon'])): ?>
                        <img src="../assets/images/icons/<?= $story['icon'] ?>" alt="<?= h($story['title']) ?>">
                    <?php endif; ?>
                </span>
                <h3 class="story-title" data-ja="<?= h($story['title']) ?>"
                    <?php if (isset($multilingualContent[$id])): ?>
                        data-en="<?= h($multilingualContent[$id]['en']['title']) ?>"
                        data-zh="<?= h($multilingualContent[$id]['zh']['title']) ?>"
                        <?php if (isset($multilingualContent[$id]['tl'])): ?>
                        data-tl="<?= h($multilingualContent[$id]['tl']['title']) ?>"
                        <?php endif; ?>
                    <?php endif; ?>
                ><?php 
                if (isset($multilingualContent[$id][$current_language]['title'])) {
                    echo h($multilingualContent[$id][$current_language]['title']);
                } else {
                    echo h($story['title']);
                }
                ?></h3>
                <p class="story-description" data-ja="<?= h($story['description']) ?>"
                    <?php if (isset($multilingualContent[$id])): ?>
                        data-en="<?= h($multilingualContent[$id]['en']['description']) ?>"
                        data-zh="<?= h($multilingualContent[$id]['zh']['description']) ?>"
                        <?php if (isset($multilingualContent[$id]['tl'])): ?>
                        data-tl="<?= h($multilingualContent[$id]['tl']['description']) ?>"
                        <?php endif; ?>
                    <?php endif; ?>
                ><?php 
                if (isset($multilingualContent[$id][$current_language]['description'])) {
                    echo h($multilingualContent[$id][$current_language]['description']);
                } else {
                    echo h($story['description']);
                }
                ?></p>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<!-- モーダル -->
<?php foreach ($stories as $id => $story): ?>
<div id="storyModal<?= $id ?>" class="story-modal">
    <div class="modal-content">
        <span class="close" onclick="closeStoryModal(<?= $id ?>)">&times;</span>
        <h2 class="modal-title">
            <?php if (!empty($story['icon'])): ?>
                <img src="../assets/images/icons/<?= $story['icon'] ?>" alt="" style="width: 32px; height: 32px; vertical-align: middle; margin-right: 10px;">
            <?php endif; ?>
            <?php 
            if (isset($multilingualContent[$id][$current_language]['title'])) {
                echo h($multilingualContent[$id][$current_language]['title']);
            } else {
                echo h($story['title']);
            }
            ?>
        </h2>
        <div class="modal-body">
            <?php 
            if (isset($multilingualContent[$id][$current_language]['content'])) {
                echo $multilingualContent[$id][$current_language]['content'];
            } else {
                echo $story['content'];
            }
            ?>
        </div>
    </div>
</div>
<?php endforeach; ?>

<script>
// 現在の言語設定（ユーザーの登録言語に基づく）
let currentLanguage = '<?= $current_language ?>';

// 多言語コンテンツデータ
const multilingualContent = <?= json_encode($multilingualContent, JSON_UNESCAPED_UNICODE) ?>;

// ページ読み込み時に登録言語で初期化
document.addEventListener('DOMContentLoaded', function() {
    updatePageLanguage(currentLanguage);
    
    // スクロールイベントハンドラーを最適化
    let ticking = false;
    function handleScroll() {
        const logo = document.getElementById('topLogo');
        const logoImg = document.getElementById('topLogoImg');
        
        if (logo && logoImg) {
            if (window.scrollY > 100) {
                logo.classList.add('scrolled');
                logoImg.classList.add('scrolled');
            } else {
                logo.classList.remove('scrolled');
                logoImg.classList.remove('scrolled');
            }
        }
        ticking = false;
    }
    
    function requestScrollTick() {
        if (!ticking) {
            requestAnimationFrame(handleScroll);
            ticking = true;
        }
    }
    
    // 既存のスクロールイベントリスナーがあれば削除して新しいものを追加
    window.removeEventListener('scroll', window.headerScrollHandler);
    window.headerScrollHandler = requestScrollTick;
    window.addEventListener('scroll', window.headerScrollHandler, { passive: true });
    
    // header.phpの標準language_switcherを使用（独自のオーバーライドは削除）
});

// グローバル関数として定義（header.phpのswitchLanguage関数から呼び出せるように）
window.setSchoolPageLanguage = function(lang) {
    currentLanguage = lang;
    updatePageLanguage(lang);
}

function setLanguage(lang) {
    currentLanguage = lang;
    updatePageLanguage(lang);
}

function updatePageLanguage(lang) {
    // メインタイトルとサブタイトルを更新
    const mainTitle = document.getElementById('mainTitle');
    const mainSubtitle = document.getElementById('mainSubtitle');
    
    if (mainTitle && mainTitle.getAttribute('data-' + lang)) {
        mainTitle.textContent = mainTitle.getAttribute('data-' + lang);
    }
    if (mainSubtitle && mainSubtitle.getAttribute('data-' + lang)) {
        mainSubtitle.textContent = mainSubtitle.getAttribute('data-' + lang);
    }
    
    // カードのタイトルと説明を更新
    document.querySelectorAll('.story-card').forEach(card => {
        const title = card.querySelector('.story-title');
        const description = card.querySelector('.story-description');
        
        if (title.getAttribute('data-' + lang)) {
            title.textContent = title.getAttribute('data-' + lang);
        }
        if (description.getAttribute('data-' + lang)) {
            description.textContent = description.getAttribute('data-' + lang);
        }
    });
}

function openStoryModal(id) {
    const modal = document.getElementById('storyModal' + id);
    
    // 多言語対応コンテンツの場合は言語に応じて内容を更新
    if (multilingualContent[id] && multilingualContent[id][currentLanguage]) {
        const content = multilingualContent[id][currentLanguage];
        const titleElement = modal.querySelector('.modal-title');
        const bodyElement = modal.querySelector('.modal-body');
        
        // 動的にアイコンを取得
        const storyIcon = <?= json_encode(array_column($stories, 'icon'), JSON_UNESCAPED_UNICODE) ?>;
        const storyIcons = <?= json_encode($stories, JSON_UNESCAPED_UNICODE) ?>;
        const iconFilename = storyIcons[id] ? storyIcons[id].icon : '';
        const iconHtml = iconFilename ? `<img src="../assets/images/icons/${iconFilename}" alt="" style="width: 32px; height: 32px; vertical-align: middle; margin-right: 10px;">` : '';
        titleElement.innerHTML = iconHtml + content.title;
        bodyElement.innerHTML = content.content;
    }
    
    modal.style.display = 'block';
    modal.style.zIndex = '2147483647';
    // 他の全ての要素のz-indexを下げる
    const header = document.querySelector('header');
    if (header) {
        header.style.zIndex = '1';
    }
    const topLogo = document.getElementById('topLogo');
    if (topLogo) {
        topLogo.style.zIndex = '1';
    }
    const childInfo = document.querySelector('.child-info-top');
    if (childInfo) {
        childInfo.style.zIndex = '1';
    }
    const languageTabs = document.querySelector('.language-tabs-global');
    if (languageTabs) {
        languageTabs.style.zIndex = '1';
    }
    const nav = document.querySelector('nav');
    if (nav) {
        nav.style.zIndex = '1';
    }
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);
}

function closeStoryModal(id) {
    const modal = document.getElementById('storyModal' + id);
    modal.classList.remove('show');
    // ヘッダー・ナビのz-indexを元に戻す
    const header = document.querySelector('header');
    if (header) {
        header.style.zIndex = '';
    }
    const topLogo = document.getElementById('topLogo');
    if (topLogo) {
        topLogo.style.zIndex = '';
    }
    const childInfo = document.querySelector('.child-info-top');
    if (childInfo) {
        childInfo.style.zIndex = '';
    }
    const languageTabs = document.querySelector('.language-tabs-global');
    if (languageTabs) {
        languageTabs.style.zIndex = '';
    }
    const nav = document.querySelector('nav');
    if (nav) {
        nav.style.zIndex = '';
    }
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

// モーダル外クリックで閉じる
window.onclick = function(event) {
    const modals = document.querySelectorAll('.story-modal');
    modals.forEach(modal => {
        if (event.target === modal && modal.classList.contains('show')) {
            modal.classList.remove('show');
            // ヘッダー・ナビのz-indexを元に戻す
            const header = document.querySelector('header');
            if (header) header.style.zIndex = '';
            const topLogo = document.getElementById('topLogo');
            if (topLogo) topLogo.style.zIndex = '';
            const childInfo = document.querySelector('.child-info-top');
            if (childInfo) childInfo.style.zIndex = '';
            const languageTabs = document.querySelector('.language-tabs-global');
            if (languageTabs) languageTabs.style.zIndex = '';
            const nav = document.querySelector('nav');
            if (nav) nav.style.zIndex = '';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }
    });
}

// ESCキーで閉じる
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modals = document.querySelectorAll('.story-modal.show');
        modals.forEach(modal => {
            modal.classList.remove('show');
            // ヘッダー・ナビのz-indexを元に戻す
            const header = document.querySelector('header');
            if (header) header.style.zIndex = '';
            const topLogo = document.getElementById('topLogo');
            if (topLogo) topLogo.style.zIndex = '';
            const childInfo = document.querySelector('.child-info-top');
            if (childInfo) childInfo.style.zIndex = '';
            const languageTabs = document.querySelector('.language-tabs-global');
            if (languageTabs) languageTabs.style.zIndex = '';
            const nav = document.querySelector('nav');
            if (nav) nav.style.zIndex = '';
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>