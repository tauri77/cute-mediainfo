import apiFetch from "@wordpress/api-fetch";

export default function searchPost( { postType, query } ) {
	return apiFetch(
		{
			path: '/wp/v2/' + encodeURIComponent( postType ) + '?search=' + encodeURIComponent( query )
		}
	);
}
