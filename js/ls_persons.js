$(function(){
	showTree = function(e) {
		$.ajax({
			url: "php/gen_tree.php",
			data: {id: e.data.id},
			success: function(data){
				console.log("gen_tree req_time", data.req_time, data);

				if (data.error) {
					alert("server: "+data.error);
					return;
				}

				$("#gtree").fadeIn()

				// Create a new directed graph
				var g = new dagreD3.graphlib.Graph().setGraph({});
				data.persons.forEach(function(person) {
					g.setNode("p"+person.id, { label: person.name });
				});
				data.family_pairs.forEach(function(family_pair) {
					g.setNode("f"+family_pair.id, {
						label: "Family "+family_pair.id
					});
					g.setEdge(
						"p"+family_pair.man_id,
						"f"+family_pair.id,
						{label: ""}
					);
					g.setEdge(
						"p"+family_pair.woman_id,
						"f"+family_pair.id,
						{label: ""}
					);
					for (var i in data.persons) {
						if (data.persons[i].parents_pair_id == family_pair.id) {
							g.setEdge(
								"f"+family_pair.id,
								"p"+data.persons[i].id,
								{label: ""}
							);
						}
					}
				});
				// Set some general styles
				g.nodes().forEach(function(v) {
				  var node = g.node(v);
				  node.rx = node.ry = 5;
				});
				// Add some custom colors based on state
				g.node('p'+data.target_id).style = "fill: #f77";
				var svg = d3.select("svg"),
					inner = svg.select("g");
				// Set up zoom support
				var zoom = d3.behavior.zoom().on("zoom", function() {
					inner.attr("transform", "translate(" + d3.event.translate + ")" +
								"scale(" + d3.event.scale + ")");
				});
				svg.call(zoom);
				// Create the renderer
				var render = new dagreD3.render();
				// Run the renderer. This is what draws the final graph.
				render(inner, g);
				// Center the graph
				var initialScale = 1;
				zoom
					.scale(initialScale)
					.event(svg);
			}
		});
	}

	loadPage = function(page) {
		$.ajax({
			url: "php/ls_persons.php",
			data: {page: page},
			success: function(data){
				console.log("ls_persons req_time", data.req_time, data);

				if (data.error && data.page_count != 0) {
					alert("server: "+data.error);
					return;
				}

				if (data.persons.length == 0) {
					return;
				}

				$("#persons_list").empty();
				for (var i in data.persons) {
					var span = $("<span/>").text(data.persons[i].name)

					if (data.persons[i].has_parents == true){
						span.click({id: data.persons[i].id}, showTree).addClass("active")
					}

					$("#persons_list").append($("<li/>").addClass(data.persons[i].sex).append(span));
				}

				$("#persons_pages").empty();
				for (var i = 0; i < data.page_count; i++) {
					var li = $("<li/>")
						.text(i+1)
						.addClass("page");

					if (i != data.page) {
						li.click({page: i}, function(e) {
							loadPage(e.data.page);
						}).addClass("active")
					}

					$("#persons_pages").append(li);
				}
			},
		});
	}

	loadPage(0);
});
