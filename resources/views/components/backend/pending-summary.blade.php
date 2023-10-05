<x-modal wire:model.defer="pendingSummaryModel" blur="sm" max-width="5xl">
    <x-card title="Summary" class="w-full sm:max-w-6xl z-10">
        <!-- common summary -->
        @if (isset($this->summaryData['common']))
            <p class="my-2">{{ $this->summaryData['common'] }}</p>
        @endif

        <!-- compare previous and current values -->
        @if (isset($this->summaryData['pre']) && isset($this->summaryData['new']))
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="m-2">
                    <p class="font-bold">Previous Values</p>
                    <hr>
                    @if (count($this->summaryData['pre']) > 0)
                        @foreach ($this->summaryData['pre'] as $key => $data)
                            @if($key == "location" && (filter_var("http://" . str_replace('"', '', $data), FILTER_VALIDATE_URL) !== false))
                                <p class="mt-2"><span class="font-medium">{{ $key }}: </span><a href="http://{{ str_replace('"', '', $data) }}" >http://{{ $data }}</a>
                                </p>
                            @else
                                <p class="mt-2"><span class="font-medium">{{ Str::title(str_replace('_', ' ',$key)) }}: </span>{{ json_encode($data) }}
                                </p>
                            @endif
                        @endforeach
                    @else
                        -
                    @endif

                </div>
                <div class="m-2">
                    <p class="font-bold">New Values</p>
                    <hr>

                    @if (count($this->summaryData['new']) > 0)
                        @foreach ($this->summaryData['new'] as $key => $data)
                            @if($key == "location" && (filter_var("http://" . str_replace('"', '', json_encode($data)), FILTER_VALIDATE_URL) !== false))
                                <p class="mt-2"><span class="font-medium">{{ $key }}: </span><a href="http://{{ str_replace('"', '', json_encode($data)) }}" >http://{{ str_replace('"', '', json_encode($data)) }}</a>
                                </p>
                            @else
                                <p class="mt-2"><span class="font-medium">{{  Str::title(str_replace('_', ' ',$key)) }}: </span>{{ json_encode($data) }}
                                </p>
                            @endif
                        @endforeach
                    @else
                        -
                    @endif
                </div>
            </div>
        @endif

        @if (isset($this->summaryData["userGroupData"]) )
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="m-2">
                    <p class="font-bold">Previous Values</p>
                    <hr>
                    @if (count($this->summaryData["userGroupData"]['pre']) > 0)
                        <div class="font-semibold mt-3 ">Name : {{ $this->summaryData["userGroupData"]['pre']["name"]  }}</div>
                        <div class='overflow-auto max-h-60 mt-3'>
                            <table>
                                <thead class="bg-gray-200">
                                <tr>
                                    <th class="px-6 py-2 text-xs text-gray-500">
                                        Type
                                    </th>
                                    <th class="px-6 py-2 text-xs text-gray-500">
                                        Auth Limit
                                    </th>
                                    <th class="px-6 py-2 text-xs text-gray-500">
                                        Daily Limit
                                    </th>
                                    <th class="px-6 py-2 text-xs text-gray-500">
                                        Max Limit
                                    </th>
                                    <th class="px-6 py-2 text-xs text-gray-500">
                                        Min Limit
                                    </th>
                                    <th class="px-6 py-2 text-xs text-gray-500">
                                        Fee
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="bg-white">
                                @foreach ($this->summaryData["userGroupData"]['pre'] as $key =>  $data)
                                    @if($key !="name")
                                        <tr class="whitespace-nowrap">
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900">
                                                    {{ \App\Enums\UserGroupLimitTypeEnum::typesValue[$key]  }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900">
                                                    {{  number_format($data["auth_limit"],2)    }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900">
                                                    {{  number_format($data["daily_limit"],2)    }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900">
                                                    {{  number_format($data["per_tran_max"],2)    }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900">
                                                    {{  number_format($data["per_tran_min"],2)    }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm text-gray-900">
                                                    {{  number_format($data["fee"],2)    }}
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        -
                    @endif

                </div>
                <div class="m-2">
                    <p class="font-bold">New Values</p>
                    <hr>

                    @if (count($this->summaryData["userGroupData"]['new']) > 0)
                        <div class="font-semibold mt-3 ">Name : {{ $this->summaryData["userGroupData"]['new']["name"]  }}</div>
                        <div class='overflow-auto max-h-60 mt-3'>
                            <table>
                                <thead class="bg-gray-200">
                                <tr>
                                    <th class="px-6 py-2 text-xs text-gray-500">
                                        Type
                                    </th>
                                    <th class="px-6 py-2 text-xs text-gray-500">
                                        Auth Limit
                                    </th>
                                    <th class="px-6 py-2 text-xs text-gray-500">
                                        Daily Limit
                                    </th>
                                    <th class="px-6 py-2 text-xs text-gray-500">
                                        Max Limit
                                    </th>
                                    <th class="px-6 py-2 text-xs text-gray-500">
                                        Min Limit
                                    </th>
                                    <th class="px-6 py-2 text-xs text-gray-500">
                                        Fee
                                    </th>
                                </tr>
                                </thead>
                                <tbody class="bg-white">
                                @foreach ($this->summaryData["userGroupData"]['new'] as $key =>  $data)
                                @if($key !="name")
                                <tr class="whitespace-nowrap">
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                           {{ \App\Enums\UserGroupLimitTypeEnum::typesValue[$key]  }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            {{  number_format($data["auth_limit"],2)    }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            {{  number_format($data["daily_limit"],2)    }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            {{  number_format($data["per_tran_max"],2)    }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            {{  number_format($data["per_tran_min"],2)    }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            {{  number_format($data["fee"],2)    }}
                                        </div>
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                                </tbody>
                            </table>
                        </div>

                    @else
                        -
                    @endif
                </div>
            </div>
        @endif

        <!-- compare images previous and current values -->
        @if (isset($this->summaryData['images']) && (count($this->summaryData['images']['pre']) > 0 || count($this->summaryData['images']['new']) > 0))
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div class="m-2">
                    <p class="font-bold">Previous Images</p>
                    <hr>
                    @if (count($this->summaryData['images']['pre']) > 0)
                        @foreach ($this->summaryData['images']['pre'] as $key => $data)
                            <p class="mt-2">{{ $key }}</p>
                            <img src="{{ env('APP_RESOURCE_URL') . $data }}" />
                        @endforeach
                    @else
                        -
                    @endif

                </div>
                <div class="m-2">
                    <p class="font-bold">New Images</p>
                    <hr>

                    @if (count($this->summaryData['images']['new']) > 0)
                        @foreach ($this->summaryData['images']['new'] as $key => $data)
                            <p class="mt-2">{{ $key }}</p>
                            <img src="{{ env('APP_RESOURCE_URL') . $data }}" />
                        @endforeach
                    @else
                        -
                    @endif
                </div>
            </div>
        @endif

        <!-- compare  full url images previous and current values -->
        @if (isset($this->summaryData['fullUrlImages']))
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div class="m-2">
                    <p class="font-bold">Previous Images</p>
                    <hr>
                    @if (count($this->summaryData['fullUrlImages']['pre']) > 0)
                        @foreach ($this->summaryData['fullUrlImages']['pre'] as $key => $data)
                            <p class="mt-2">{{ $key }}</p>
                            <img src="{{ $data }}" />
                        @endforeach
                    @else
                        -
                    @endif

                </div>
                <div class="m-2">
                    <p class="font-bold">New Images</p>
                    <hr>

                    @if (count($this->summaryData['fullUrlImages']['new']) > 0)
                        @foreach ($this->summaryData['fullUrlImages']['new'] as $key => $data)
                            <p class="mt-2">{{ $key }}</p>
                            <img src="{{ $data }}" />
                        @endforeach
                    @else
                        -
                    @endif
                </div>
            </div>
        @endif
        <x-slot name="footer">
            <div class="flex justify-end gap-x-4">
                <x-button flat label="Cancel" x-on:click="close" />
            </div>
        </x-slot>
    </x-card>
</x-modal>
