// Comparador con clip-path (no escala ninguna imagen)
(function(){
  function clamp(n, min, max){ return Math.max(min, Math.min(max, n)); }

  function init(root){
    var inner  = root.querySelector('.lc-inner');
    var topImg = root.querySelector('.lc-img--top');
    var handle = root.querySelector('.lc-handle');
    if(!inner || !topImg || !handle) return;

    var start = parseFloat(root.getAttribute('data-start') || '50');
    if(isNaN(start)) start = 50;
    set(start);

    function set(percent){
      percent = clamp(percent, 0, 100);
      // clip-path: inset(0 RIGHT 0 0) -> RIGHT = 100 - percent
      var rightInset = (100 - percent) + '%';
      topImg.style.clipPath = 'inset(0 ' + rightInset + ' 0 0)';
      handle.style.left = percent + '%';
    }

    function posFromEvent(e){
      var rect = inner.getBoundingClientRect();
      var clientX = (e.touches && e.touches[0]) ? e.touches[0].clientX : e.clientX;
      return clamp(((clientX - rect.left) / rect.width) * 100, 0, 100);
    }

    function onMove(e){ set(posFromEvent(e)); }
    function startDrag(e){
      e.preventDefault && e.preventDefault();
      document.addEventListener('mousemove', onMove);
      document.addEventListener('touchmove', onMove, {passive:false});
      document.addEventListener('mouseup', endDrag);
      document.addEventListener('touchend', endDrag);
      onMove(e);
    }
    function endDrag(){
      document.removeEventListener('mousemove', onMove);
      document.removeEventListener('touchmove', onMove);
      document.removeEventListener('mouseup', endDrag);
      document.removeEventListener('touchend', endDrag);
    }

    // Drag en el handle y click/drag en toda el área
    handle.addEventListener('mousedown', startDrag);
    handle.addEventListener('touchstart', startDrag, {passive:true});
    inner.addEventListener('click', onMove);
  }

  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.lc').forEach(init);
  });
})();
