<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900">
        <h3 class="font-semibold text-gray-800 mb-4">طلبات المعمل النظري</h3>

        <div class="mb-4">
            <label for="theory-pending-search" class="sr-only">بحث بالاسم أو رقم التذكرة أو رقم الجواز</label>
            <input
                id="theory-pending-search"
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="ابحث بالاسم أو رقم التذكرة أو رقم الجواز..."
                class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-gray-500 focus:ring-gray-500"
            />
        </div>

        @if ($pendingCases->isEmpty())
            <p class="text-sm text-gray-500">لا توجد طلبات محولة حالياً من الاستقبال.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-right text-gray-500 border-b border-gray-200">
                            <th class="py-2 pr-0 pl-3 font-medium">رقم التذكرة</th>
                            <th class="py-2 pl-3 font-medium">اسم العامل</th>
                            <th class="py-2 pl-3 font-medium">رقم الجواز</th>
                            <th class="py-2 pl-3 font-medium">وقت التحويل</th>
                            <th class="py-2 pl-3 font-medium">حالة النظري</th>
                            <th class="py-2 pl-0 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($pendingCases as $item)
                            <tr class="hover:bg-gray-50 {{ $item->latestReturn ? 'bg-amber-50' : '' }}">
                                <td class="py-3 pr-0 pl-3 whitespace-nowrap">{{ $item->ticket_number }}</td>
                                <td class="py-3 pl-3">
                                    <div>
                                        <span class="font-medium text-gray-900">{{ $item->full_name }}</span>
                                        @if ($item->latestReturn)
                                            <span class="mr-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                                مُرجع من رفع البيانات
                                            </span>
                                        @endif
                                    </div>
                                    @if ($item->latestReturn?->note)
                                        <p class="mt-1 text-xs text-gray-500">ملاحظة الإرجاع: {{ $item->latestReturn->note }}</p>
                                    @endif
                                    @if ($item->latestReturn?->returnedBy)
                                        <p class="mt-0.5 text-xs text-gray-400">بواسطة: {{ $item->latestReturn->returnedBy->name }}</p>
                                    @endif
                                </td>
                                <td class="py-3 pl-3 whitespace-nowrap">{{ $item->passport_number }}</td>
                                <td class="py-3 pl-3 whitespace-nowrap text-gray-500">
                                    {{ \Illuminate\Support\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}
                                </td>
                                <td class="py-3 pl-3">
                                    @if ($item->has_theory_photo)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">تم</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">لم يتم</span>
                                    @endif
                                </td>
                                <td class="py-3 pl-0 text-left">
                                    <a
                                        href="{{ route('theory', $item) }}"
                                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
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
