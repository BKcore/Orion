/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if(jQuery && $)
{    
    jLT = {
        target: null,
        loader: null,
        init: null,
        getText: null,
        getTime: null,
        genLink: null
    };
    
    function jLTCallback(result) 
    {
        try {
            
            var container = $('<div class="tweets" />');
            $.each(result, function(i, tweet) {
                container.append(
                    $('<div class="tweet" />').append(
                        $('<span class="tweet-time" />').html(jLT.genLink('https://twitter.com/'+tweet.user.screen_name+'/status/'+tweet.id_str, jLT.getTime(tweet.created_at), 'status'))
                    ).append(
                        $('<p class="tweet-text" />').html(jLT.getText(tweet))
                    )
                );
            });console.log(container);console.log(jLT);
            jLT.loader.hide();
            jLT.target.hide().html(container).fadeIn();
        }
        catch(e)
        {console.log(e);
            try {
                jLT.loader.hide();
                jLT.target.html('Unable to parse tweets.').hide().fadeIn();
            }
            catch(e)
            {
                console.log(e);
            }
        }
    }
    
    jLT.genLink = function(href, label, type)
    {
        return '<a href="'+href+'" target="_blank" class="tweet-link-'+type+'">'+label+'</a>';
    }

    jLT.getText = function(tweet) 
    {
        var text = new String(tweet.text);

        $.each(tweet.entities.urls, function(i, url){
            text = text.replace(url.url, jLT.genLink(url.expanded_url, url.display_url, 'url'));
        });
        $.each(tweet.entities.user_mentions, function(i, usr){
            text = text.replace('@'+usr.screen_name, jLT.genLink('https://twitter.com/'+usr.screen_name, '@'+usr.screen_name, 'mention'));
        });
        $.each(tweet.entities.hashtags, function(i, hash){
            text = text.replace('#'+hash.text, jLT.genLink('https://twitter.com/search?q=%23'+hash.text, '#'+hash.text, 'hash'));
        });
        
        return text;
    }

    jLT.getTime = function(time_value) 
    {
        var values = time_value.split(" ");
        time_value = values[1] + " " + values[2] + ", " + values[5] + " " + values[3];
        var parsed_date = Date.parse(time_value);
        var relative_to = (arguments.length > 1) ? arguments[1] : new Date();
        var delta = parseInt((relative_to.getTime() - parsed_date) / 1000);
        delta = delta + (relative_to.getTimezoneOffset() * 60);

        if (delta < 60) {
            return 'Now';
        } else if(delta < 120) {
            return 'About a minute ago';
        } else if(delta < (60*60)) {
            return (parseInt(delta / 60)).toString() + ' minutes ago';
        } else if(delta < (120*60)) {
            return 'About an hour ago';
        } else if(delta < (24*60*60)) {
            return 'About ' + (parseInt(delta / 3600)).toString() + ' hours ago';
        } else if(delta < (48*60*60)) {
            return '1 day ago';
        } else {
            return (parseInt(delta / 86400)).toString() + ' days ago';
        }
    }

    jLT.init = function(settings, loaderSelector)
    {
        var api_url = 'https://api.twitter.com/1/statuses/user_timeline.json';
        var data = {
            'screen_name': 'twitterapi',
            'include_rts': 'true',
            'include_entities': 'true',
            'count': '3'
        };
        $.extend(data, settings);
        
        jLT.target = $(this) || $('#twitter-thread');
        jLT.loader = $(loaderSelector) || $('#twitter-loader');
        
        $.ajax({
            type: 'GET',
            url: api_url,
            data: data,
            cache: false,
            dataType: 'jsonp',
            jsonpCallback: 'jLTCallback'
        });
    }

    $.extend($.fn, {jLastTweet: jLT.init});
}