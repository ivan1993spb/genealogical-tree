
FROM tutum/lamp:latest

RUN rm -fr /app; mkdir /app

ADD . /app
RUN mv /app/run.sh /run.sh

EXPOSE 80 3306

CMD ["/run.sh"]
