$(function() {
	$.ajax({
		url: "php/family_pairs.php",
		success: function(data) {
			console.log("family_pairs req_time", data.req_time, data);

			if (data.error) {
				alert("server: "+data.error);
				return;
			}

			for (var i in data.family_pairs) {
				var text = data.family_pairs[i].man_name+" and "+data.family_pairs[i].woman_name
				$("#form_parents").append($("<option/>").text(text).val(data.family_pairs[i].id));
			}
		},
	});

	$("#form_button").click(function(){
		if ($("#form_name").val().length == 0) {
			alert("error: empty name");
			return;
		}

		var data = {
			'name': $("#form_name").val(),
			'sex':  $("#form_sex").val()
		};

		if ($("#form_parents").val()) {
			data['parents_pair_id'] = $("#form_parents").val();
		}

		$.ajax({
			url: "php/add_person.php",
			data: data,
			success: function(data) {
				console.log("add_person req_time", data.req_time, data);

				if (data.error) {
					alert("server: "+data.error);
					return;
				}

				$("#form_name").val("");
				$('#form_sex').prop('selectedIndex',0);
				$('#form_parents').prop('selectedIndex',0);

				alert("Success");
			}
		});
	});
});
