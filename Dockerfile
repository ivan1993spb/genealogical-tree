
FROM tutum/lamp:latest

RUN rm -fr /app; mkdir /app

ADD . /app
ADD http://code.jquery.com/jquery-2.2.0.min.js /app/js/
ADD http://d3js.org/d3.v3.min.js /app/js/
ADD http://cpettitt.github.io/project/dagre-d3/latest/dagre-d3.js /app/js/
RUN chmod 664 /app/js/*
RUN mv /app/run.sh /run.sh

EXPOSE 80 3306

CMD ["/run.sh"]
