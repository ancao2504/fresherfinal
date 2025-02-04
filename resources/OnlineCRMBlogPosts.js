/* 
	File: OnlineCRMBlogPosts.js
	Author: Vu Mai 
	Date: 2022-08-06 
	Purpose: to get new post count from website api 
*/

jQuery($ => {
	getNewPostsCount();
});

function getNewPostsCount () {
	let url = 'https://cloudgo.vn/api/blogs?action=getNewPostsCount';

	app.request.get({ 'url': url })
	.then(function (err, data) {
			if (err) {
				$('#blog-posts-counter').addClass('hide');
				console.log('Error getNewPostsCount:', err);
				return;
			}

			if (data.count != 0) {
				$('#blog-posts-counter').removeClass('hide');
				$('#blog-posts-counter').text(data.count);
			}	
		}
	);	
}