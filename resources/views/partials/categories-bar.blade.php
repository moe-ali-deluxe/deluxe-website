<nav x-data="catbar()" class="bg-white border-b shadow-sm z-50">
  @php
    // Use composer-provided nav categories; fall back safely
    $raw = ($navCategories ?? $categories ?? collect());

    // Keep only top-level parents
    $parents = collect($raw)->filter(fn($c) => ($c->parent_id ?? null) === null);

    // Reindex
    $parents = $parents->values();

    // Split into visible + overflow "More"
    $top  = $parents->take(8)->values();
    $more = $parents->slice(8)->values();

    // Toggle for showing category icons in bar
    $showIcons = false;
  @endphp

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Row -->
    <div class="flex items-center h-14">
      <!-- Logo area -->
      <a href="{{ url('/') }}" class="mr-4 flex-shrink-0 text-2xl font-bold text-gray-900"></a>

      <!-- Desktop: main nav -->
      <div class="hidden md:flex items-center gap-1" role="menubar" aria-label="Product categories">
        {{-- Primary parents --}}
        @foreach ($top as $parent)
          @php $idx = $loop->index; @endphp
          <div class="relative"
               @mouseenter="openMenu({{ $idx }})"
               @mouseleave="closeMenuDelayed({{ $idx }})"
               @focusin="openMenu({{ $idx }})"
               @focusout="closeMenuDelayed({{ $idx }})">

            <a href="{{ route('products.byCategory', ['slug' => $parent->slug]) }}"
               class="inline-flex items-center {{ $showIcons ? 'gap-2' : 'gap-0' }} px-3 py-2 rounded-md text-sm font-medium
                      text-gray-800 hover:text-blue-700 hover:bg-blue-50 transition"
               role="menuitem" aria-haspopup="{{ $parent->children->count() ? 'true' : 'false' }}"
               aria-expanded="false">
              @if($showIcons && !empty($parent->image))
                <img src="{{ asset('storage/'.$parent->image) }}" alt="" class="h-4 w-4 object-contain" loading="lazy">
              @endif
              <span class="truncate max-w-[10rem]">{{ $parent->name }}</span>
              @if($parent->children->count())
                <svg class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.128l3.71-3.9a.75.75 0 111.08 1.04l-4.24 4.46a.75.75 0 01-1.08 0l-4.24-4.46a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                </svg>
              @endif
            </a>

            {{-- Mega dropdown --}}
            @if ($parent->children->count())
              <div x-show="isOpen({{ $idx }})" x-transition.opacity x-cloak
                   class="absolute left-0 top-full mt-2 w-[28rem] bg-white border border-gray-200
                          rounded-lg shadow-lg p-4"
                   @mouseenter="cancelClose({{ $idx }})"
                   @mouseleave="closeMenu({{ $idx }})">
                <div class="grid grid-cols-2 gap-4 max-h-80 overflow-y-auto">
                  @foreach ($parent->children as $child)
                    <div>
                      <a href="{{ route('products.byCategory', ['slug' => $child->slug]) }}"
                         class="flex items-center {{ $showIcons ? 'gap-2' : 'gap-0' }} text-gray-900 font-semibold text-sm hover:text-blue-700">
                        @if($showIcons && !empty($child->image))
                          <img src="{{ asset('storage/'.$child->image) }}" alt="" class="h-4 w-4 object-contain" loading="lazy">
                        @endif
                        <span class="truncate">{{ $child->name }}</span>
                      </a>
                      @if($child->children->count())
                        <ul class="mt-2 space-y-1">
                          @foreach ($child->children as $sub)
                            <li>
                              <a href="{{ route('products.byCategory', ['slug' => $sub->slug]) }}"
                                 class="block text-sm text-gray-600 hover:text-blue-700 truncate">
                                {{ $sub->name }}
                              </a>
                            </li>
                          @endforeach
                        </ul>
                      @endif
                    </div>
                  @endforeach
                </div>
              </div>
            @endif
          </div>
        @endforeach

        {{-- More menu --}}
        @if($more->count())
          <div class="relative"
               @mouseenter="openMenu('more')"
               @mouseleave="closeMenuDelayed('more')"
               @focusin="openMenu('more')"
               @focusout="closeMenuDelayed('more')">
            <button type="button"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-md text-sm font-medium
                           text-gray-800 hover:text-blue-700 hover:bg-blue-50 transition">
              More
              <svg class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.128l3.71-3.9a.75.75 0 111.08 1.04l-4.24 4.46a.75.75 0 01-1.08 0l-4.24-4.46a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
              </svg>
            </button>

            <div x-show="isOpen('more')" x-transition.opacity x-cloak
                 class="absolute left-0 top-full mt-2 w-72 bg-white border border-gray-200 rounded-lg shadow-lg p-2">
              <ul class="max-h-80 overflow-y-auto">
                @foreach($more as $parent)
                  <li class="relative group">
                    <a href="{{ route('products.byCategory', ['slug' => $parent->slug]) }}"
                       class="flex items-center justify-between gap-2 px-2 py-2 rounded hover:bg-gray-50">
                      <span class="truncate text-sm text-gray-800">{{ $parent->name }}</span>
                      @if($parent->children->count())
                        <svg class="h-4 w-4 text-gray-400 group-hover:text-gray-600" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01-.02-1.06L11.128 10 7.19 6.29a.75.75 0 111.04-1.08l4.46 4.24a.75.75 0 010 1.08l-4.46 4.24a.75.75 0 01-1.06 0z" clip-rule="evenodd"/>
                        </svg>
                      @endif
                    </a>
                    @if($parent->children->count())
                      <!-- flyout -->
                      <div class="absolute left-full top-0 ml-2 hidden group-hover:block bg-white border border-gray-200 rounded-lg shadow-lg p-3 w-72">
                        <div class="space-y-2 max-h-80 overflow-y-auto">
                          @foreach($parent->children as $child)
                            <div>
                              <a href="{{ route('products.byCategory', ['slug' => $child->slug]) }}"
                                 class="block text-sm font-semibold text-gray-900 hover:text-blue-700 truncate">
                                {{ $child->name }}
                              </a>
                              @if($child->children->count())
                                <ul class="mt-1 space-y-1">
                                  @foreach($child->children as $sub)
                                    <li>
                                      <a href="{{ route('products.byCategory', ['slug' => $sub->slug]) }}"
                                         class="block text-sm text-gray-600 hover:text-blue-700 truncate">
                                        {{ $sub->name }}
                                      </a>
                                    </li>
                                  @endforeach
                                </ul>
                              @endif
                            </div>
                          @endforeach
                        </div>
                      </div>
                    @endif
                  </li>
                @endforeach
              </ul>
            </div>
          </div>
        @endif
      </div>

      <!-- Mobile toggler -->
      <div class="ml-auto md:hidden">
        <button @click="mobileOpen = !mobileOpen" type="button"
                class="text-gray-600 hover:text-gray-900 focus:outline-none focus:ring rounded p-1.5"
                :aria-expanded="mobileOpen.toString()" aria-label="Toggle menu">
          <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
          <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>
    </div>
  </div>

  <!-- Mobile accordion -->
  <div x-show="mobileOpen" x-cloak class="md:hidden bg-white border-t border-gray-200 shadow-inner">
    <div class="px-4 py-3 space-y-1 max-h-[70vh] overflow-y-auto">
      @foreach($parents as $p)
        <div x-data="{ open: false }" class="border-b border-gray-100">
          <div class="flex items-center justify-between">
            <a href="{{ route('products.byCategory', ['slug' => $p->slug]) }}"
               class="py-3 text-gray-900 font-semibold">{{ $p->name }}</a>
            @if($p->children->count())
              <button @click="open = !open" class="p-2 text-gray-600" aria-label="Toggle subcategories">
                <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': open}" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.128l3.71-3.9a.75.75 0 111.08 1.04l-4.24 4.46a.75.75 0 01-1.08 0l-4.24-4.46a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                </svg>
              </button>
            @endif
          </div>
          @if($p->children->count())
            <div x-show="open" x-cloak class="pb-3 pl-2">
              @foreach($p->children as $c)
                <div x-data="{ open2: false }" class="mb-1">
                  <div class="flex items-center justify-between">
                    <a href="{{ route('products.byCategory', ['slug' => $c->slug]) }}"
                       class="block py-2 text-gray-700 hover:text-blue-700">{{ $c->name }}</a>
                    @if($c->children->count())
                      <button @click="open2 = !open2" class="p-2 text-gray-600" aria-label="Toggle subcategories">
                        <svg class="w-4 h-4 transition-transform" :class="{'rotate-180': open2}" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.128l3.71-3.9a.75.75 0 111.08 1.04l-4.24 4.46a.75.75 0 010 1.08l-4.46 4.24a.75.75 0 01-1.06 0z" clip-rule="evenodd"/>
                        </svg>
                      </button>
                    @endif
                  </div>
                  @if($c->children->count())
                    <div x-show="open2" x-cloak class="pl-3">
                      @foreach($c->children as $s)
                        <a href="{{ route('products.byCategory', ['slug' => $s->slug]) }}"
                           class="block py-1 text-sm text-gray-600 hover:text-blue-700">{{ $s->name }}</a>
                      @endforeach
                    </div>
                  @endif
                </div>
              @endforeach
            </div>
          @endif
        </div>
      @endforeach
    </div>
  </div>
</nav>

<script>
function catbar() {
  return {
    openId: null,
    mobileOpen: false,
    timers: new Map(),
    isOpen(id) { return this.openId === id; },
    openMenu(id) { this.clearTimer(id); this.openId = id; },
    closeMenu(id) { this.clearTimer(id); if (this.openId === id) this.openId = null; },
    closeMenuDelayed(id) {
      this.clearTimer(id);
      const t = setTimeout(() => { if (this.openId === id) this.openId = null; }, 150);
      this.timers.set(id, t);
    },
    cancelClose(id) { this.clearTimer(id); },
    clearTimer(id) {
      const t = this.timers.get(id);
      if (t) { clearTimeout(t); this.timers.delete(id); }
    }
  }
}
</script>
