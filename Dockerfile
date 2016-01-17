
FROM tutum/lamp:latest

# Download: code.jquery.com/jquery-2.2.0.min.js
# Download: d3js.org/d3.v3.min.js
# Download: cpettitt.github.io/project/dagre-d3/latest/dagre-d3.js

RUN rm -fr /app
COPY * /app
RUN mysql -uroot < /app/db.sql

EXPOSE 80 3306
CMD ["/run.sh"]
