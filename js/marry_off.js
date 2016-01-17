$(function() {
	var updateSingle = function() {
		$('#form_man')
			.empty()
			.append('<option value="">chouse man</option>');
		$('#form_woman')
			.empty()
			.append('<option value="">chouse woman</option>');

		$.ajax({
			url: "php/single.php",
			success: function(data){
				console.log("single req_time", data.req_time, data);

				if (data.error) {
					alert("server: "+data.error);
					return;
				}

				for (var i in data.persons) {
					var option = $("<option/>")
						.val(data.persons[i].id)
						.text(data.persons[i].name);

					if (data.persons[i].sex == "man") {
						$("#form_man").append(option);
					} else if (data.persons[i].sex == "woman") {
						$("#form_woman").append(option);
					}
				}
			},
		});
	}

	updateSingle();

	$("#form_button").click(function(){
		if (!$("#form_man").val().length || !$("#form_woman").val().length) {
			alert("error: select man and woman");
			return;
		}

		$.ajax({
			url: "php/define_family_pair.php",
			data: {
				'man_id': $("#form_man").val(),
				'woman_id': $("#form_woman").val()
			},
			success: function(data){
				console.log("define_family_pair req_time", data.req_time, data);

				if (data.error) {
					alert("server: "+data.error);
					return;
				}

				alert("Success");
				updateSingle();
			},
		});
	});
});
