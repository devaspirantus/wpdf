<div class="dga-card">
    <div class="dga-card-body text-ink">
        <h3 class="font-semibold text-ink mb-4">طلبات المعمل النظري</h3>

        <div class="mb-4">
            <label for="theory-pending-search" class="sr-only">بحث بالاسم أو رقم التذكرة أو رقم الجواز</label>
            <input
                id="theory-pending-search"
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="ابحث بالاسم أو رقم التذكرة أو رقم الجواز..."
                class="w-full rounded-lg border border-line px-3 py-2 text-sm focus:border-primary focus:ring-2 focus:ring-primary/20"
            />
        </div>

        @if ($pendingCases->isEmpty())
            <p class="text-sm text-muted">لا توجد طلبات محولة حالياً من الاستقبال.</p>
        @else
            <div class="overflow-x-auto">
                <table class="dga-table min-w-full text-sm">
                    <thead>
                        <tr>
                            <th class="py-2 pr-0 pl-3 font-medium">رقم التذكرة</th>
                            <th class="py-2 pl-3 font-medium">اسم العامل</th>
                            <th class="py-2 pl-3 font-medium">رقم الجواز</th>
                            <th class="py-2 pl-3 font-medium">وقت التحويل</th>
                            <th class="py-2 pl-3 font-medium">حالة النظري</th>
                            <th class="py-2 pl-0 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pendingCases as $item)
                            <tr class="hover:bg-primary-pastel/15 {{ $item->latestReturn ? 'bg-warning/10' : '' }}">
                                <td class="py-3 pr-0 pl-3 whitespace-nowrap">{{ $item->ticket_number }}</td>
                                <td class="py-3 pl-3">
                                    <div>
                                        <span class="font-medium text-ink">{{ $item->full_name }}</span>
                                        @if ($item->latestReturn)
                                            <span class="mr-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-warning/20 text-navy">
                                                مُرجع من رفع البيانات
                                            </span>
                                        @endif
                                    </div>
                                    @if ($item->latestReturn?->note)
                                        <p class="mt-1 text-xs text-muted">ملاحظة الإرجاع: {{ $item->latestReturn->note }}</p>
                                    @endif
                                    @if ($item->latestReturn?->returnedBy)
                                        <p class="mt-0.5 text-xs text-muted">بواسطة: {{ $item->latestReturn->returnedBy->name }}</p>
                                    @endif
                                </td>
                                <td class="py-3 pl-3 whitespace-nowrap">{{ $item->passport_number }}</td>
                                <td class="py-3 pl-3 whitespace-nowrap text-muted">
                                    {{ \Illuminate\Support\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3 pl-3">
                                    @if ($item->has_theory_photo)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success/10 text-success">تم</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-surface text-muted">لم يتم</span>
                                    @endif
                                </td>
                                <td class="py-3 pl-0 text-left">
                                    <a
                                        href="{{ route('theory', $item) }}"
                                        class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                    >
                                        فتح المعمل
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
