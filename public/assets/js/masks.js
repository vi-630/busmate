(function(){
	// Aplica máscara de telefone brasileiro a inputs com data-mask="telefone"
	function maskTelefone(v){
		if (!v) return '';
		let d = v.replace(/\D/g,'');
		if (d.length > 11) d = d.slice(0,11);
		// celular 11: (00) 0 0000-0000
		if (d.length >= 11) {
			return '(' + d.slice(0,2) + ') ' + d.slice(2,3) + ' ' + d.slice(3,7) + '-' + d.slice(7,11);
		}
		// fixo 10: (00) 0000-0000
		if (d.length >= 10) {
			return '(' + d.slice(0,2) + ') ' + d.slice(2,6) + '-' + d.slice(6,10);
		}
		// DDD parcial
		if (d.length > 2) {
			return '(' + d.slice(0,2) + ') ' + d.slice(2);
		}
		return d;
	}

	function onInput(e){
		const el = e.target;
		const pos = el.selectionStart;
		const old = el.value;
		el.value = maskTelefone(old);
		// tenta manter cursor no fim (simples)
		el.selectionStart = el.selectionEnd = el.value.length;
	}

	document.addEventListener('DOMContentLoaded', function(){
		const inputs = document.querySelectorAll('input[data-mask="telefone"]');
		inputs.forEach(function(inp){
			inp.addEventListener('input', onInput, false);
			// inicialmente aplica
			inp.value = maskTelefone(inp.value || '');
		});
	});
})();
