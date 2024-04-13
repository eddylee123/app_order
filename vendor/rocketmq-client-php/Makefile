srcdir = /application/data/dor_manager/vendor/rocketmq-client-php
builddir = /application/data/dor_manager/vendor/rocketmq-client-php
top_srcdir = /application/data/dor_manager/vendor/rocketmq-client-php
top_builddir = /application/data/dor_manager/vendor/rocketmq-client-php
EGREP = /usr/bin/grep -E
SED = /usr/bin/sed
CONFIGURE_COMMAND = './configure'
CONFIGURE_OPTIONS =
SHLIB_SUFFIX_NAME = so
SHLIB_DL_SUFFIX_NAME = so
ZEND_EXT_TYPE = zend_extension
RE2C = exit 0;
AWK = gawk
shared_objects_rocketmq_client_php = batch_message.lo common.lo consume_from_where.lo consume_status.lo e_log_level.lo message.lo message_ext.lo message_model.lo message_queues.lo mq_exception.lo producer.lo pull_consumer.lo pull_result.lo pull_status.lo push_consumer.lo push_status.lo rocketmq_client_php.lo send_result.lo send_status.lo trace_model.lo transaction_status.lo
PHP_PECL_EXTENSION = rocketmq_client_php
PHP_MODULES = $(phplibdir)/rocketmq_client_php.la
PHP_ZEND_EX =
all_targets = $(PHP_MODULES) $(PHP_ZEND_EX)
install_targets = install-modules install-headers
prefix = /usr
exec_prefix = $(prefix)
libdir = ${exec_prefix}/lib
prefix = /usr
phplibdir = /application/data/dor_manager/vendor/rocketmq-client-php/modules
phpincludedir = /usr/include/php
CC = cc
CFLAGS = -g -O2
CFLAGS_CLEAN = $(CFLAGS)
CPP = cc -E
CPPFLAGS = -lrocketmq -g -O0 -DHAVE_CONFIG_H
CXX =
CXXFLAGS =
CXXFLAGS_CLEAN = $(CXXFLAGS)
EXTENSION_DIR = /usr/lib64/php/modules
PHP_EXECUTABLE = /usr/bin/php
EXTRA_LDFLAGS =
EXTRA_LIBS =
INCLUDES = -I/usr/include/php -I/usr/include/php/main -I/usr/include/php/TSRM -I/usr/include/php/Zend -I/usr/include/php/ext -I/usr/include/php/ext/date/lib
LFLAGS =
LDFLAGS =
SHARED_LIBTOOL =
LIBTOOL = $(SHELL) $(top_builddir)/libtool
SHELL = /bin/sh
INSTALL_HEADERS =
mkinstalldirs = $(top_srcdir)/build/shtool mkdir -p
INSTALL = $(top_srcdir)/build/shtool install -c
INSTALL_DATA = $(INSTALL) -m 644

DEFS = -DPHP_ATOM_INC -I$(top_builddir)/include -I$(top_builddir)/main -I$(top_srcdir)
COMMON_FLAGS = $(DEFS) $(INCLUDES) $(EXTRA_INCLUDES) $(CPPFLAGS) $(PHP_FRAMEWORKPATH)

all: $(all_targets)
	@echo
	@echo "Build complete."
	@echo "Don't forget to run 'make test'."
	@echo

build-modules: $(PHP_MODULES) $(PHP_ZEND_EX)

build-binaries: $(PHP_BINARIES)

libphp$(PHP_MAJOR_VERSION).la: $(PHP_GLOBAL_OBJS) $(PHP_SAPI_OBJS)
	$(LIBTOOL) --mode=link $(CC) $(CFLAGS) $(EXTRA_CFLAGS) -rpath $(phptempdir) $(EXTRA_LDFLAGS) $(LDFLAGS) $(PHP_RPATHS) $(PHP_GLOBAL_OBJS) $(PHP_SAPI_OBJS) $(EXTRA_LIBS) $(ZEND_EXTRA_LIBS) -o $@
	-@$(LIBTOOL) --silent --mode=install cp $@ $(phptempdir)/$@ >/dev/null 2>&1

libs/libphp$(PHP_MAJOR_VERSION).bundle: $(PHP_GLOBAL_OBJS) $(PHP_SAPI_OBJS)
	$(CC) $(MH_BUNDLE_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS) $(LDFLAGS) $(EXTRA_LDFLAGS) $(PHP_GLOBAL_OBJS:.lo=.o) $(PHP_SAPI_OBJS:.lo=.o) $(PHP_FRAMEWORKS) $(EXTRA_LIBS) $(ZEND_EXTRA_LIBS) -o $@ && cp $@ libs/libphp$(PHP_MAJOR_VERSION).so

install: $(all_targets) $(install_targets)

install-sapi: $(OVERALL_TARGET)
	@echo "Installing PHP SAPI module:       $(PHP_SAPI)"
	-@$(mkinstalldirs) $(INSTALL_ROOT)$(bindir)
	-@if test ! -r $(phptempdir)/libphp$(PHP_MAJOR_VERSION).$(SHLIB_DL_SUFFIX_NAME); then \
		for i in 0.0.0 0.0 0; do \
			if test -r $(phptempdir)/libphp$(PHP_MAJOR_VERSION).$(SHLIB_DL_SUFFIX_NAME).$$i; then \
				$(LN_S) $(phptempdir)/libphp$(PHP_MAJOR_VERSION).$(SHLIB_DL_SUFFIX_NAME).$$i $(phptempdir)/libphp$(PHP_MAJOR_VERSION).$(SHLIB_DL_SUFFIX_NAME); \
				break; \
			fi; \
		done; \
	fi
	@$(INSTALL_IT)

install-binaries: build-binaries $(install_binary_targets)

install-modules: build-modules
	@test -d modules && \
	$(mkinstalldirs) $(INSTALL_ROOT)$(EXTENSION_DIR)
	@echo "Installing shared extensions:     $(INSTALL_ROOT)$(EXTENSION_DIR)/"
	@rm -f modules/*.la >/dev/null 2>&1
	@$(INSTALL) modules/* $(INSTALL_ROOT)$(EXTENSION_DIR)

install-headers:
	-@if test "$(INSTALL_HEADERS)"; then \
		for i in `echo $(INSTALL_HEADERS)`; do \
			i=`$(top_srcdir)/build/shtool path -d $$i`; \
			paths="$$paths $(INSTALL_ROOT)$(phpincludedir)/$$i"; \
		done; \
		$(mkinstalldirs) $$paths && \
		echo "Installing header files:          $(INSTALL_ROOT)$(phpincludedir)/" && \
		for i in `echo $(INSTALL_HEADERS)`; do \
			if test "$(PHP_PECL_EXTENSION)"; then \
				src=`echo $$i | $(SED) -e "s#ext/$(PHP_PECL_EXTENSION)/##g"`; \
			else \
				src=$$i; \
			fi; \
			if test -f "$(top_srcdir)/$$src"; then \
				$(INSTALL_DATA) $(top_srcdir)/$$src $(INSTALL_ROOT)$(phpincludedir)/$$i; \
			elif test -f "$(top_builddir)/$$src"; then \
				$(INSTALL_DATA) $(top_builddir)/$$src $(INSTALL_ROOT)$(phpincludedir)/$$i; \
			else \
				(cd $(top_srcdir)/$$src && $(INSTALL_DATA) *.h $(INSTALL_ROOT)$(phpincludedir)/$$i; \
				cd $(top_builddir)/$$src && $(INSTALL_DATA) *.h $(INSTALL_ROOT)$(phpincludedir)/$$i) 2>/dev/null || true; \
			fi \
		done; \
	fi

PHP_TEST_SETTINGS = -d 'open_basedir=' -d 'output_buffering=0' -d 'memory_limit=-1'
PHP_TEST_SHARED_EXTENSIONS =  ` \
	if test "x$(PHP_MODULES)" != "x"; then \
		for i in $(PHP_MODULES)""; do \
			. $$i; $(top_srcdir)/build/shtool echo -n -- " -d extension=$$dlname"; \
		done; \
	fi; \
	if test "x$(PHP_ZEND_EX)" != "x"; then \
		for i in $(PHP_ZEND_EX)""; do \
			. $$i; $(top_srcdir)/build/shtool echo -n -- " -d $(ZEND_EXT_TYPE)=$(top_builddir)/modules/$$dlname"; \
		done; \
	fi`
PHP_DEPRECATED_DIRECTIVES_REGEX = '^(magic_quotes_(gpc|runtime|sybase)?|(zend_)?extension(_debug)?(_ts)?)[\t\ ]*='

test: all
	@if test ! -z "$(PHP_EXECUTABLE)" && test -x "$(PHP_EXECUTABLE)"; then \
		INI_FILE=`$(PHP_EXECUTABLE) -d 'display_errors=stderr' -r 'echo php_ini_loaded_file();' 2> /dev/null`; \
		if test "$$INI_FILE"; then \
			$(EGREP) -h -v $(PHP_DEPRECATED_DIRECTIVES_REGEX) "$$INI_FILE" > $(top_builddir)/tmp-php.ini; \
		else \
			echo > $(top_builddir)/tmp-php.ini; \
		fi; \
		INI_SCANNED_PATH=`$(PHP_EXECUTABLE) -d 'display_errors=stderr' -r '$$a = explode(",\n", trim(php_ini_scanned_files())); echo $$a[0];' 2> /dev/null`; \
		if test "$$INI_SCANNED_PATH"; then \
			INI_SCANNED_PATH=`$(top_srcdir)/build/shtool path -d $$INI_SCANNED_PATH`; \
			$(EGREP) -h -v $(PHP_DEPRECATED_DIRECTIVES_REGEX) "$$INI_SCANNED_PATH"/*.ini >> $(top_builddir)/tmp-php.ini; \
		fi; \
		TEST_PHP_EXECUTABLE=$(PHP_EXECUTABLE) \
		TEST_PHP_SRCDIR=$(top_srcdir) \
		CC="$(CC)" \
			$(PHP_EXECUTABLE) -n -c $(top_builddir)/tmp-php.ini $(PHP_TEST_SETTINGS) $(top_srcdir)/run-tests.php -n -c $(top_builddir)/tmp-php.ini -d extension_dir=$(top_builddir)/modules/ $(PHP_TEST_SHARED_EXTENSIONS) $(TESTS); \
		TEST_RESULT_EXIT_CODE=$$?; \
		rm $(top_builddir)/tmp-php.ini; \
		exit $$TEST_RESULT_EXIT_CODE; \
	else \
		echo "ERROR: Cannot run tests without CLI sapi."; \
	fi

clean:
	find . -name \*.gcno -o -name \*.gcda | xargs rm -f
	find . -name \*.lo -o -name \*.o | xargs rm -f
	find . -name \*.la -o -name \*.a | xargs rm -f
	find . -name \*.so | xargs rm -f
	find . -name .libs -a -type d|xargs rm -rf
	rm -f libphp$(PHP_MAJOR_VERSION).la $(SAPI_CLI_PATH) $(SAPI_CGI_PATH) $(SAPI_LITESPEED_PATH) $(SAPI_FPM_PATH) $(OVERALL_TARGET) modules/* libs/*

distclean: clean
	rm -f Makefile config.cache config.log config.status Makefile.objects Makefile.fragments libtool main/php_config.h main/internal_functions_cli.c main/internal_functions.c stamp-h buildmk.stamp Zend/zend_dtrace_gen.h Zend/zend_dtrace_gen.h.bak Zend/zend_config.h TSRM/tsrm_config.h
	rm -f php7.spec main/build-defs.h scripts/phpize
	rm -f ext/date/lib/timelib_config.h ext/mbstring/oniguruma/config.h ext/mbstring/libmbfl/config.h ext/oci8/oci8_dtrace_gen.h ext/oci8/oci8_dtrace_gen.h.bak
	rm -f scripts/man1/phpize.1 scripts/php-config scripts/man1/php-config.1 sapi/cli/php.1 sapi/cgi/php-cgi.1 sapi/phpdbg/phpdbg.1 ext/phar/phar.1 ext/phar/phar.phar.1
	rm -f sapi/fpm/php-fpm.conf sapi/fpm/init.d.php-fpm sapi/fpm/php-fpm.service sapi/fpm/php-fpm.8 sapi/fpm/status.html
	rm -f ext/iconv/php_have_bsd_iconv.h ext/iconv/php_have_glibc_iconv.h ext/iconv/php_have_ibm_iconv.h ext/iconv/php_have_iconv.h ext/iconv/php_have_libiconv.h ext/iconv/php_iconv_aliased_libiconv.h ext/iconv/php_iconv_supports_errno.h ext/iconv/php_php_iconv_h_path.h ext/iconv/php_php_iconv_impl.h
	rm -f ext/phar/phar.phar ext/phar/phar.php
	if test "$(srcdir)" != "$(builddir)"; then \
	  rm -f ext/phar/phar/phar.inc; \
	fi
	$(EGREP) define'.*include/php' $(top_srcdir)/configure | $(SED) 's/.*>//'|xargs rm -f

prof-gen:
	CCACHE_DISABLE=1 $(MAKE) PROF_FLAGS=-fprofile-generate all

prof-clean:
	find . -name \*.lo -o -name \*.o | xargs rm -f
	find . -name \*.la -o -name \*.a | xargs rm -f
	find . -name \*.so | xargs rm -f
	rm -f libphp$(PHP_MAJOR_VERSION).la $(SAPI_CLI_PATH) $(SAPI_CGI_PATH) $(SAPI_LITESPEED_PATH) $(SAPI_FPM_PATH) $(OVERALL_TARGET) modules/* libs/*

prof-use:
	CCACHE_DISABLE=1 $(MAKE) PROF_FLAGS=-fprofile-use all


.PHONY: all clean install distclean test prof-gen prof-clean prof-use
.NOEXPORT:
batch_message.lo: /application/data/dor_manager/vendor/rocketmq-client-php/batch_message.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/batch_message.c -o batch_message.lo 
common.lo: /application/data/dor_manager/vendor/rocketmq-client-php/common.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/common.c -o common.lo 
consume_from_where.lo: /application/data/dor_manager/vendor/rocketmq-client-php/consume_from_where.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/consume_from_where.c -o consume_from_where.lo 
consume_status.lo: /application/data/dor_manager/vendor/rocketmq-client-php/consume_status.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/consume_status.c -o consume_status.lo 
e_log_level.lo: /application/data/dor_manager/vendor/rocketmq-client-php/e_log_level.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/e_log_level.c -o e_log_level.lo 
message.lo: /application/data/dor_manager/vendor/rocketmq-client-php/message.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/message.c -o message.lo 
message_ext.lo: /application/data/dor_manager/vendor/rocketmq-client-php/message_ext.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/message_ext.c -o message_ext.lo 
message_model.lo: /application/data/dor_manager/vendor/rocketmq-client-php/message_model.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/message_model.c -o message_model.lo 
message_queues.lo: /application/data/dor_manager/vendor/rocketmq-client-php/message_queues.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/message_queues.c -o message_queues.lo 
mq_exception.lo: /application/data/dor_manager/vendor/rocketmq-client-php/mq_exception.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/mq_exception.c -o mq_exception.lo 
producer.lo: /application/data/dor_manager/vendor/rocketmq-client-php/producer.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/producer.c -o producer.lo 
pull_consumer.lo: /application/data/dor_manager/vendor/rocketmq-client-php/pull_consumer.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/pull_consumer.c -o pull_consumer.lo 
pull_result.lo: /application/data/dor_manager/vendor/rocketmq-client-php/pull_result.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/pull_result.c -o pull_result.lo 
pull_status.lo: /application/data/dor_manager/vendor/rocketmq-client-php/pull_status.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/pull_status.c -o pull_status.lo 
push_consumer.lo: /application/data/dor_manager/vendor/rocketmq-client-php/push_consumer.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/push_consumer.c -o push_consumer.lo 
push_status.lo: /application/data/dor_manager/vendor/rocketmq-client-php/push_status.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/push_status.c -o push_status.lo 
rocketmq_client_php.lo: /application/data/dor_manager/vendor/rocketmq-client-php/rocketmq_client_php.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/rocketmq_client_php.c -o rocketmq_client_php.lo 
send_result.lo: /application/data/dor_manager/vendor/rocketmq-client-php/send_result.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/send_result.c -o send_result.lo 
send_status.lo: /application/data/dor_manager/vendor/rocketmq-client-php/send_status.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/send_status.c -o send_status.lo 
trace_model.lo: /application/data/dor_manager/vendor/rocketmq-client-php/trace_model.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/trace_model.c -o trace_model.lo 
transaction_status.lo: /application/data/dor_manager/vendor/rocketmq-client-php/transaction_status.c
	$(LIBTOOL) --mode=compile $(CC)  -I. -I/application/data/dor_manager/vendor/rocketmq-client-php $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS)  -c /application/data/dor_manager/vendor/rocketmq-client-php/transaction_status.c -o transaction_status.lo 
$(phplibdir)/rocketmq_client_php.la: ./rocketmq_client_php.la
	$(LIBTOOL) --mode=install cp ./rocketmq_client_php.la $(phplibdir)

./rocketmq_client_php.la: $(shared_objects_rocketmq_client_php) $(ROCKETMQ_CLIENT_PHP_SHARED_DEPENDENCIES)
	$(LIBTOOL) --mode=link $(CC) $(COMMON_FLAGS) $(CFLAGS_CLEAN) $(EXTRA_CFLAGS) $(LDFLAGS)  -o $@ -export-dynamic -avoid-version -prefer-pic -module -rpath $(phplibdir) $(EXTRA_LDFLAGS) $(shared_objects_rocketmq_client_php) $(ROCKETMQ_CLIENT_PHP_SHARED_LIBADD)

