<script>
  document.getElementById('topbar')?.remove();
  document.getElementById('bottom-nav')?.remove();
  document.getElementById('page').style.cssText = 'padding:0;max-width:100%;margin:0';
</script>

<style>
    body { overflow: hidden; }

    #chat-root {
        display: flex;
        flex-direction: column;
        height: 100dvh;
        max-width: var(--content-w);
        margin: 0 auto;
        background: var(--bg);
    }

    #chat-hd {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 14px;
        background: rgba(255,255,255,.96);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border-bottom: 1.5px solid var(--border);
        z-index: 10;
    }

    #chat-msgs {
        flex: 1;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        padding: 16px 14px 12px;
        gap: 2px;
        overscroll-behavior: contain;
    }

    #chat-bar {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 14px;
        padding-bottom: calc(10px + env(safe-area-inset-bottom, 0px));
        background: rgba(255, 255, 255, .98);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border-top: 1px solid #f0f0f0;
    }

    #chat-input-wrap {
        flex: 1;
        display: flex;
        align-items: flex-end;
        background: #f4f4f5;
        border-radius: 24px;
        padding: 6px 18px;
        border: 1.5px solid transparent;
        transition: border-color .15s, background .15s, box-shadow .15s;
        margin-right: -40px;
    }

    #chat-input {
        flex: 1;
        background: transparent;
        border: none;
        padding: 4px 0;
        font-size: 16px; /* no zoom iOS */
        font-family: var(--sans);
        font-weight: 500;
        color: #09090b;
        resize: none;
        max-height: 120px;
        overflow-y: auto;
        line-height: 1.5;
        outline: none;
    }
    #chat-input::placeholder { color: #a1a1aa; font-weight: 400; }

    #chat-send {
        width: 36px; height: 36px;
        border-radius: 50%;
        background: var(--accent);
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        cursor: pointer; border: none;
        box-shadow: 0 2px 8px rgba(225,29,72,.3);
        transition: transform .1s, opacity .12s;
        margin-bottom: 1px;
    }
    #chat-send:active { transform: scale(.88); opacity: .8; }
</style>

<div id="chat-root">
    <div id="chat-hd">
        <a href="/?p=messages" style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:50%;color:var(--ink-2);flex-shrink:0;text-decoration:none;transition:background .12s" onmouseenter="this.style.background='var(--surface-3)'" onmouseleave="this.style.background='transparent'">
            <span class="ms" style="font-size:20px">arrow_back_ios_new</span>
        </a>
        <div id="chat-hd-info" style="flex:1;min-width:0"></div>
    </div>

    <div id="chat-msgs"></div>

    <div id="chat-bar">
        <div id="chat-input-wrap">
            <textarea id="chat-input" rows="1" placeholder="Scrivi un messaggio..."></textarea>
        </div>
        <button id="chat-send">
            <span class="ms" style="font-size:18px">send</span>
        </button>
    </div>
</div>