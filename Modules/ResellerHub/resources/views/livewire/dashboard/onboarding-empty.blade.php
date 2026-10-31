<div class="hidden"
     aria-hidden="true"
     wire:poll.10s="refreshWidget"
     @onboarding-tour-updated.window="$wire.refreshWidget()"
     x-data="{}"
     x-init="if (window.BroadcastChannel) { const ch = new BroadcastChannel('crm_onboarding_sync'); ch.onmessage = (e) => { if (e.data && e.data.type === 'tour-updated') { $wire.refreshWidget(); } }; }"></div>
