import apiFetch from "@wordpress/api-fetch";

export default function initialPost( { postType } ) {
	return apiFetch( { path: "/wp/v2/" + postType } );
}
