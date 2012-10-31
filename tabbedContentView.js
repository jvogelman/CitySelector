//(function($){
	//Backbone.sync = function(method, model, success, error){ 
	//	success();
	//}
	  
	var Tab = Backbone.Model.extend({
		defaults: {
			index: -1,
			name: '',
			html: ''
	    }
	});
	  
	var TabCollection = Backbone.Collection.extend({
		 model: Tab
	});
	  
	var TabView = Backbone.View.extend({
		tagName: 'li', // name of tag to be created  
	
		events: {},
	
		
		initialize: function(){
			_.bindAll(this, 'render', 'unrender'); // every function that uses 'this' as the current object should be in here
			
			this.model.bind('change', this.render);
			this.model.bind('remove', this.unrender);
			
			this.active = false;
	    },
	    

	    
	    render: function(){
	    	if (this.active) {
	    		$(this.el).html('<li class="active"><a data-toggle="tab" href="#Section' + this.model.get('index') + '">' + 
	    				this.model.get('name') + '</a></li>');
	    	} else {
	    		$(this.el).html('<li><a data-toggle="tab" href="#Section' + this.model.get('index') + '">' + 
	    				this.model.get('name') + '</a></li>');
	    	}
	    	
	        return this; // for chainable calls, like .render().el
	    },
	    
	    unrender: function(){
	        $(this.el).remove();
	    }
	    
	});
	
	var TabContentView = Backbone.View.extend({
		tagName: 'div',
		
		events: {},	
			
		
		initialize: function(){
			_.bindAll(this, 'render', 'unrender'); // every function that uses 'this' as the current object should be in here
			
			this.model.bind('change', this.render);
			this.model.bind('remove', this.unrender);
			
			this.active = false;
			
		},
    
		render: function(){
	    	if (this.active) {
	    		$(this.el).html('<div id="Section' + this.model.get('index') + '" class="tab-pane active">' + this.model.get('html') + '</div>');
	    	} else {
	    		$(this.el).html('<div id="Section' + this.model.get('index') + '" class="tab-pane">' + this.model.get('html') + '</div>');
	    	}
	        return this; // for chainable calls, like .render().el
	    },
	    
	    unrender: function(){
	        $(this.el).remove();
	    }
	});
		
		
	
		
	var TabbedContentView = Backbone.View.extend({
		//el: $('#wikipedia'), // el attaches to existing element
	    events: {
	      
	    },
	
	    initialize: function(element){
	    	this.el = element;
	    	_.bindAll(this, 'render', 'addItem', 'appendItem'); // every function that uses 'this' as the current object should be in here
	        
	        this.collection = new TabCollection();
	        this.collection.bind('add', this.appendItem); // collection event binder

	        this.counter = 0;
	        this.render();
	    },
	    
	
	    render: function(){
	    	var self = this;
	    	$(this.el).html('<ul class="nav nav-tabs" data-tabs="tabs"></ul><div class="tab-content"></div>');
	    	_(this.collection.models).each(function(item){ // in case collection is not empty
	    		self.appendItem(item);
	    	}, this);
	    },
	    
	    addItem: function(index, name, html, active){
	        this.counter++;
	        var item = new Tab();
	        item.set({
	        	index: index,
	        	name: name,
	        	html: html
	        });
	        //this.collection.add(item, active); #### change back to this? can't have second parameter
	        this.appendItem(item, active);
	        return item;
	    },
	    
	    appendItem: function(item, active){
	    	var tabView = new TabView({
	    		model: item
	    	});
	    	var tabContentView = new TabContentView({
	    		model: item
	    	});
	    	
	    	tabView.active = active;
	    	tabContentView.active = active;
	    	
	    	var temp = $('#wikipedia');

			//$('#wikipedia .nav').append('<li><a data-toggle="tab" href="#Section1">blah</a></li>');
	    	var el = tabView.render().el;
	    	//$('#wikipedia .nav').append(tabView.render().el.innerHTML);
	    	$('.nav', this.el).append(tabView.render().el.innerHTML);
	    	$('.tab-content', this.el).append(tabContentView.render().el.innerHTML);
	    },
	    
	    clearItems: function(){
	    	this.collection.reset();
	        this.render();
	    }
	});

	    
//})(jQuery);
	
	
	