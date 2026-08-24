document.addEventListener( 'submit', async ( event ) => {
	const form = event.target;
	if ( !( form instanceof HTMLFormElement ) || !form.matches( '[data-agentic-airport-form]' ) ) {
		return;
	}

	event.preventDefault();
	const results = form.parentElement.querySelector( '[data-agentic-airport-results]' );
	const button = form.querySelector( 'button[type="submit"]' );
	results.textContent = form.dataset.loading;
	button.disabled = true;

	try {
		const response = await fetch( form.dataset.endpoint, {
			method: 'POST',
			credentials: 'same-origin',
			body: new FormData( form ),
		} );
		const payload = await response.json();
		if ( !response.ok || !payload.success ) {
			throw new Error(
				payload.data && payload.data.message ? payload.data.message : form.dataset.error
			);
		}
		results.innerHTML = payload.data.html;
	} catch ( error ) {
		results.textContent = error instanceof Error ? error.message : form.dataset.error;
	} finally {
		button.disabled = false;
	}
} );
