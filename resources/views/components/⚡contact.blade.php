<div>
    <x-site-shell title="اتصل بنا" subtitle="يسعدنا تواصلك. أرسل رسالتك وسنعود إليك في أقرب وقت ممكن.">
        <div class="grid gap-10 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="rounded-3xl bg-white/70 backdrop-blur-xl border border-black/5 p-6 sm:p-8">
                @if ($sent)
                    <div class="rounded-2xl bg-green-50 border border-green-200 text-green-800 p-5 text-sm leading-relaxed">
                        تم إرسال رسالتك بنجاح. شكرًا لتواصلك معنا، وسنرد عليك قريبًا.
                    </div>
                @else
                    <form wire:submit="submit" class="space-y-5">
                        <div>
                            <label for="name" class="block text-sm font-medium text-[#111111] mb-2">الاسم</label>
                            <input
                                id="name"
                                type="text"
                                wire:model="name"
                                class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-[#111111] outline-none focus:border-[#111111] transition-colors"
                                placeholder="اسمك الكامل"
                            />
                            @error('name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-[#111111] mb-2">البريد الإلكتروني</label>
                            <input
                                id="email"
                                type="email"
                                wire:model="email"
                                dir="ltr"
                                class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-[#111111] outline-none focus:border-[#111111] transition-colors"
                                placeholder="your@email.com"
                            />
                            @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-[#111111] mb-2">الموضوع</label>
                            <input
                                id="subject"
                                type="text"
                                wire:model="subject"
                                class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-[#111111] outline-none focus:border-[#111111] transition-colors"
                                placeholder="بماذا يمكننا مساعدتك؟"
                            />
                            @error('subject') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-[#111111] mb-2">الرسالة</label>
                            <textarea
                                id="message"
                                wire:model="message"
                                rows="6"
                                class="w-full rounded-2xl border border-black/10 bg-white px-4 py-3 text-[#111111] outline-none focus:border-[#111111] transition-colors resize-y"
                                placeholder="اكتب رسالتك هنا..."
                            ></textarea>
                            @error('message') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <button
                            type="submit"
                            class="inline-flex items-center justify-center h-12 px-8 rounded-full bg-[#111111] text-white text-sm font-medium hover:bg-[#333333] transition-colors"
                        >
                            <span wire:loading.remove wire:target="submit">إرسال الرسالة</span>
                            <span wire:loading wire:target="submit">جاري الإرسال...</span>
                        </button>
                    </form>
                @endif
            </div>

            <aside class="space-y-6">
                <div class="rounded-3xl bg-zinc-900 text-white p-6 sm:p-8">
                    <h2 class="text-lg font-medium mb-4">طرق أخرى للتواصل</h2>
                    <ul class="space-y-4 text-sm text-zinc-300">
                        <li class="flex items-start gap-3">
                            <iconify-icon icon="solar:letter-linear" class="text-lg mt-0.5 shrink-0"></iconify-icon>
                            <div>
                                <p class="text-white mb-1">البريد الإلكتروني</p>
                                <a href="mailto:{{ config('mail.from.address') }}" class="hover:text-white transition-colors" dir="ltr">{{ config('mail.from.address') }}</a>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <iconify-icon icon="solar:question-circle-linear" class="text-lg mt-0.5 shrink-0"></iconify-icon>
                            <div>
                                <p class="text-white mb-1">الأسئلة الشائعة</p>
                                <a href="{{ route('home') }}#faq" wire:navigate class="hover:text-white transition-colors">اطّلع على الإجابات الشائعة</a>
                            </div>
                        </li>
                        <li class="flex items-start gap-3">
                            <iconify-icon icon="solar:document-text-linear" class="text-lg mt-0.5 shrink-0"></iconify-icon>
                            <div>
                                <p class="text-white mb-1">المستندات القانونية</p>
                                <a href="{{ route('terms') }}" wire:navigate class="hover:text-white transition-colors block">الشروط والأحكام</a>
                                <a href="{{ route('privacy') }}" wire:navigate class="hover:text-white transition-colors block mt-1">سياسة الخصوصية</a>
                            </div>
                        </li>
                    </ul>
                </div>

                <div class="rounded-3xl bg-white/70 backdrop-blur-xl border border-black/5 p-6">
                    <p class="text-sm text-stone-500 leading-relaxed">
                        عادةً نرد خلال يوم عمل واحد. إذا كان استفسارك يتعلق بحسابك، يفضّل ذكر البريد المرتبط بالحساب لتسريع المساعدة.
                    </p>
                </div>
            </aside>
        </div>
    </x-site-shell>
</div>

<?php

use App\Mail\ContactMessage;
use Illuminate\Support\Facades\Mail;

new
#[\Livewire\Attributes\Title('اتصل بنا')]
class extends \Livewire\Component {
    public string $name = '';

    public string $email = '';

    public string $subject = '';

    public string $message = '';

    public bool $sent = false;

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'subject' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'name.required' => 'يرجى إدخال الاسم.',
            'email.required' => 'يرجى إدخال البريد الإلكتروني.',
            'email.email' => 'يرجى إدخال بريد إلكتروني صالح.',
            'subject.required' => 'يرجى إدخال الموضوع.',
            'message.required' => 'يرجى كتابة الرسالة.',
        ];
    }

    public function submit(): void
    {
        $validated = $this->validate();

        Mail::to(config('mail.from.address'))->send(new ContactMessage($validated));

        $this->reset(['name', 'email', 'subject', 'message']);
        $this->sent = true;
    }
};
?>
