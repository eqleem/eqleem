<?php

namespace App\Events;

use App\Actions\CreateDefaultBlocks;
use App\Models\Tenant;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// use App\Models\Block;
// use App\Models\Link;
// use App\Models\Review;

class TenantCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Tenant $tenant)
    {
        CreateDefaultBlocks::run($this->tenant);

        // $this->createAboutBlocks();
        // $this->createAgreementBlock();
        // $this->createCtaBlock();
        // $this->createReviewsBlock();
        // $this->createFaqBlock();
        // $this->createLinksBlock();
        // $this->createFeaturesBlock();
    }

    public function createAboutBlocks()
    {
        $block = new Block;
        $block->tenant_id = $this->tenant->id;
        $block->active = true;
        $block->name = 'about';
        $block->config->set('title', 'من نحن');
        $block->config->set('text', 'نعمل بروح الالتزام والاحتراف لتقديم منتجات وخدمات عالية الجودة تلبي احتياجات العملاء وتواكب تطورات السوق. نسعى لبناء علاقات طويلة الأمد قائمة على الثقة والمصداقية، ونؤمن أن رضى العميل هو أساس نجاحنا واستمرارنا، لذا نحرص على التطوير المستمر وتقديم قيمة حقيقية في كل ما نقدمه.');
        $block->save();
    }

    public function createAgreementBlock()
    {
        $block = new Block;
        $block->tenant_id = $this->tenant->id;
        $block->active = true;
        $block->name = 'agreement';
        $block->config->set('agreement_title', 'الإتفاقية وسياسة الاستخدام');
        $block->config->set('agreement_content', '<h3>1. المقدمة</h3><p>مرحباً بكم في موقعنا. باستخدامكم لهذا الموقع أو أي من خدماته، فإنكم توافقون على الالتزام التام بشروط وأحكام هذه الاتفاقية. يرجى قراءة البنود بعناية قبل استخدام الموقع أو إتمام أي عملية شراء أو طلب خدمة.</p><p>يُعتبر دخولكم إلى الموقع أو استخدامكم له موافقة صريحة على هذه الشروط، وفي حال عدم الموافقة، يُرجى التوقف فوراً عن استخدام الموقع.</p><hr><h3>2. التعريفات</h3><ul><li><strong>الموقع</strong>: يقصد به المنصة أو المتجر الإلكتروني الذي يتم من خلاله عرض وبيع المنتجات أو تقديم الخدمات.</li><li><strong>العميل</strong>: هو كل شخص يستخدم الموقع، سواء للشراء أو للاطلاع أو للاستفادة من الخدمات.</li><li><strong>المنتجات / الخدمات</strong>: تشمل جميع السلع أو الخدمات المعروضة من قبل الموقع للبيع أو الحجز أو الاستخدام.</li></ul><hr><h3>3. أهلية الاستخدام</h3><p>باستخدام هذا الموقع، فإنك تقر بأنك بلغت السن القانوني المعتبر في بلدك، وتملك الأهلية الكاملة لإبرام العقود والتعاملات التجارية، وأنك تستخدم الموقع لأغراض مشروعة وغير مخالفة للأنظمة والقوانين المعمول بها.</p><hr><h3>4. الحسابات والبيانات</h3><ul><li>عند إنشاء حساب أو إدخال بياناتك، فإنك تلتزم بتقديم معلومات دقيقة وصحيحة ومحدثة.</li><li>يتحمل المستخدم المسؤولية الكاملة عن سرية معلومات الدخول إلى حسابه.</li><li>يحتفظ الموقع بالحق في تعليق أو إلغاء أي حساب في حال وجود نشاط مخالف أو احتيالي أو انتهاك للشروط.</li></ul><hr><h3>5. الطلبات والدفع</h3><ul><li>تعتبر جميع الطلبات خاضعة للقبول والموافقة من قبل إدارة الموقع.</li><li>يلتزم العميل بدفع المبلغ الكامل للمنتجات أو الخدمات حسب الأسعار المعروضة وقت الشراء.</li><li>يحتفظ الموقع بالحق في تعديل الأسعار أو العروض دون إشعار مسبق.</li><li>في حال الدفع الإلكتروني، فإن عملية الدفع تتم عبر مزود خدمة دفع آمن، ولا يحتفظ الموقع بمعلومات بطاقات الدفع.</li></ul><hr><h3>6. الشحن والتسليم (للمنتجات المادية)</h3><ul><li>يتم تحديد مدة الشحن والتسليم حسب المنطقة وطريقة الشحن المختارة.</li><li>لا يتحمل الموقع مسؤولية التأخير الناتج عن شركة الشحن أو الظروف الخارجة عن الإرادة.</li><li>يجب على العميل التأكد من صحة عنوان التسليم ومعلومات التواصل.</li></ul><hr><h3>7. الإلغاء والاسترجاع</h3><ul><li>يمكن للعميل طلب إلغاء أو استرجاع الطلب وفقاً لسياسة الإرجاع المعتمدة، بشرط أن تكون السلعة بحالتها الأصلية وغير مستخدمة.</li><li>بعض الخدمات أو المنتجات الرقمية قد لا تكون قابلة للاسترجاع بعد الإتمام.</li><li>يحتفظ الموقع بالحق في رفض أي عملية استرجاع إذا لم تتوفر الشروط اللازمة.</li></ul><hr><h3>8. حقوق الملكية الفكرية</h3><p>جميع المحتويات المعروضة في الموقع، بما في ذلك النصوص، الشعارات، التصاميم، الصور، والبرمجيات، هي ملك خاص للموقع أو مرخصة له، ويحظر استخدامها أو نسخها أو إعادة نشرها دون إذن خطي مسبق.</p><hr><h3>9. استخدام الموقع</h3><ul><li>يجب استخدام الموقع للأغراض المشروعة فقط.</li><li>يمنع استخدامه لنشر محتوى مسيء أو مضلل أو منتهك لحقوق الآخرين.</li><li>يحق للموقع حظر أو حذف أي محتوى أو حساب يثبت مخالفته لهذه السياسة دون إشعار مسبق.</li></ul><hr><h3>10. حدود المسؤولية</h3><ul><li>لا يتحمل الموقع أي مسؤولية عن أية أضرار مباشرة أو غير مباشرة ناتجة عن استخدام الموقع أو الخدمات.</li><li>لا يضمن الموقع خلو المحتوى من الأخطاء أو الانقطاعات أو الأعطال التقنية.</li></ul><hr><h3>11. التعديلات على الشروط</h3><p>يحتفظ الموقع بالحق في تعديل أو تحديث هذه الاتفاقية في أي وقت، وتصبح التعديلات سارية فور نشرها. يُعد استمراركم في استخدام الموقع بعد التعديل موافقة ضمنية على الشروط الجديدة.</p><hr><h3>12. التواصل</h3><p>للاستفسارات أو الشكاوى يمكنكم التواصل معنا عبر القنوات التواصل الظاهرة في الموقع&nbsp;<br>&nbsp;</p><hr><h3>13. القوانين المطبقة</h3><p>تخضع هذه الاتفاقية وتفسَّر وفقاً للأنظمة والقوانين المعمول بها في [بلدك]، ويكون الاختصاص القضائي للمحاكم المختصة في تلك الدولة.</p>');
        $block->save();
    }

    public function createCtaBlock()
    {
        $block = new Block;
        $block->tenant_id = $this->tenant->id;
        $block->active = true;
        $block->name = 'cta';
        $block->save();

        // links
        $contactLink = new Link;
        $contactLink->tenant_id = $this->tenant->id;
        $contactLink->block_id = $block->id;
        $contactLink->active = true;
        $contactLink->name = 'contact';
        $contactLink->slug = 'contact';
        $contactLink->link = $this->tenant->user->email;
        $contactLink->type = 'cta';
        $contactLink->meta = [
            'subject' => 'Contact from website',
        ];
        $contactLink->save();

        $subscriptionLink = new Link;
        $subscriptionLink->tenant_id = $this->tenant->id;
        $subscriptionLink->block_id = $block->id;
        $subscriptionLink->active = true;
        $subscriptionLink->name = 'subscription';
        $subscriptionLink->slug = 'subscription';
        $subscriptionLink->link = '';
        $subscriptionLink->type = 'cta';
        $subscriptionLink->sort = 3;
        $subscriptionLink->meta = [
            'message' => 'اشترك في قائمتنا البريدية لتصلك آخر الأخبار والعروض.',
        ];
        $subscriptionLink->save();

    }

    public function createReviewsBlock()
    {
        $block = new Block;
        $block->tenant_id = $this->tenant->id;
        $block->active = true;
        $block->name = 'reviews';
        $block->save();

        $review = new Review;
        $review->tenant_id = $this->tenant->id;
        $review->block_id = $block->id;
        $review->active = true;
        $review->client_name = 'علي العمري';
        $review->client_email = 'exampleReview@gmail.com';
        $review->client_phone = '0512345678';
        $review->score = 5;
        $review->review_text = 'جودة الخدمات تفوق التوقعات، والتعامل احترافي وسريع من بداية الطلب حتى التسليم. فريق العمل متعاون ويهتم بأدق التفاصيل. أنصح الجميع بالتعامل معكم، فعلاً نموذج يُحتذى في المصداقية والجودة.';
        $review->sort = 1;
        $review->save();
    }

    public function createFaqBlock()
    {
        $block = new Block;
        $block->tenant_id = $this->tenant->id;
        $block->active = true;
        $block->name = 'faq';
        $block->config->set('title', 'الأسئلة الشائعة');
        $block->config->set('subtitle', 'هنا قائمة بإجاباتنا على أكثر الأسئلة تكراراً، إذا لم تجد إجابة لسؤالك تواصل معنا في أي وقت.');
        $block->config->set('faqs', [
            [
                'active' => true,
                'question' => 'ما نوع المنتجات أو الخدمات التي تقدمونها؟',
                'answer' => 'نقدم مجموعة متنوعة من المنتجات والخدمات التجارية التي تلبي احتياجات الأفراد والشركات، مع الحرص على الجودة العالية والمعايير الاحترافية في كل ما نقدمه.',
            ],
            [
                'active' => true,
                'question' => 'كيف يمكنني طلب منتج أو خدمة؟',
                'answer' => 'يمكنك الشراء من خلال موقعنا الإلكتروني أو من خلال الاتصال بنا للحصول على المزيد من المعلومات.',
            ],
            [
                'active' => true,
                'question' => 'هل توفرون خدمة التوصيل؟',
                'answer' => 'نعم، نوفر خدمة التوصيل داخل وخارج المدينة حسب نوع الطلب والموقع، مع ضمان سرعة التسليم وسلامة المنتج.',
            ],
            [
                'active' => true,
                'question' => 'ما طرق الدفع المتاحة؟',
                'answer' => 'نقبل عدة طرق دفع تشمل التحويل البنكي، الدفع الإلكتروني، والدفع عند الاستلام في بعض الحالات.',
            ],
            [
                'active' => true,
                'question' => 'هل يمكن استرجاع أو استبدال المنتج؟',
                'answer' => 'نعم، يمكن استرجاع أو استبدال المنتجات وفقًا لسياسة الاسترجاع الخاصة بنا، بشرط أن يكون المنتج في حالته الأصلية وضمن الفترة المحددة.',
            ],
            [
                'active' => true,
                'question' => 'كيف يمكنني التواصل معكم؟',
                'answer' => 'يمكنك التواصل معنا عبر قنواتنا الرسمية مثل رقم الجوال، البريد الإلكتروني، أو من خلال نموذج “اتصل بنا” في الموقع الإلكتروني، وسنكون سعداء بخدمتك في أي وقت.',
            ],
        ]);
        $block->save();
    }

    public function createFeaturesBlock()
    {
        $block = new Block;
        $block->tenant_id = $this->tenant->id;
        $block->active = true;
        $block->name = 'features';
        $block->config->set('title', 'المزايا الرئيسية');
        $block->config->set('subtitle', 'نحن نقدم خدمات متكاملة لعملائنا بجودة عالية وبأسعار مناسبة.');
        $block->config->set('features', [
            [
                'active' => true,
                'icon' => 'ShieldCheck',
                'title' => 'جودة لا تضاهى',
                'description' => 'منتجاتنا وخدماتنا مصممة بأعلى معايير الجودة لتمنحك تجربة استثنائية وتفوق توقعاتك.',
            ],
            [
                'active' => true,
                'icon' => 'RotateClockwise2',
                'title' => 'الالتزام الدائم',
                'description' => 'نسلم جميع الطلبات في الوقت المحدد، لأن وقتك قيمة ونحن نحترمه.',
            ],
            [
                'active' => true,
                'icon' => 'Versions',
                'title' => 'قيمة حقيقية',
                'description' => 'نقدم أفضل الأسعار مقابل أعلى مستوى من الجودة، لتستمتع بخدمة اقتصادية وموثوقة.',
            ],
            [
                'active' => true,
                'icon' => 'Heartbeat',
                'title' => 'خدمة عملاء من القلب',
                'description' => 'فريقنا موجود دائمًا للاستماع لك، والإجابة عن استفساراتك، وحل أي مشكلة بسرعة واحترافية.',
            ],
        ]);
        $block->save();

    }

    public function createLinksBlock()
    {
        $block = new Block;
        $block->tenant_id = $this->tenant->id;
        $block->active = true;
        $block->name = 'links';
        $block->save();

        $link = new Link;
        $link->tenant_id = $this->tenant->id;
        $link->block_id = $block->id;
        $link->active = true;
        $link->name = 'instagram';
        $link->slug = 'instagram';
        $link->link = 'https://www.instagram.com/'.$this->tenant->handle;
        $link->type = 'link';
        $link->meta = [
            'network' => 'instagram',
        ];
        $link->save();

        $link = new Link;
        $link->tenant_id = $this->tenant->id;
        $link->block_id = $block->id;
        $link->active = true;
        $link->name = 'x';
        $link->slug = 'x';
        $link->link = 'https://x.com/'.$this->tenant->handle;
        $link->type = 'link';
        $link->meta = [
            'network' => 'x',
        ];
        $link->save();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
