#!/bin/env python
# -*- coding: utf-8 -*-

import sys,os
from PIL import Image
import ftplib
import getopt
from elementtree import ElementTree
import unicodedata

class Options:

	def __init__(self, argv, usage = "" ):

		# Message à afficher en cas de mauvaise utilisation des options
		self.usage = usage

		self.options = {}
		self.argv = argv

	def parse(self):

		# construction de la chaine argument pour getopt
		gos_short = ''
		gos_long = ''

		for o in self.options:
			opt = self.options[o]

			# getopt demande deux chaines pour les typographies longues et courtes
			gos_short += opt['short']
			gos_long += opt['long']
			
			# si l'option prend un paramètre
			if not opt['flag']:
				gos_short += ':'
				gos_long += '='

		# parse la ligne de commande
		try:
			opts, args = getopt.getopt( self.argv[1:], gos_short, gos_long )
		except getopt.GetoptError:
			print 'Unkown option'
			self.print_usage()
			sys.exit(2)
			
		# création des dicionnaires pour les valeurs
		self.param = {}
		self.is_flag = {}
		s2l = {} # associations court:long
		for o in self.options:
			s2l[ self.options[o]['short'] ] = self.options[o]['long']


		# prend les valeurs indiquées sur la ligne de commande
		for o,a in opts:
			# court => on enlève un tiret
			os = o[1:]
			# long => on enlève deux tirets
			ol = o[2:]

			# si c'est une option courte
			if os in s2l:
				# si c'est un flag
				if self.options[ s2l[os] ]['flag']:
					# demandé => vrai
					self.options[ s2l[os] ]['value'] = True
				else:
					# prend la valeur indiquée
					self.options[ s2l[os] ]['value'] = a
			# si c'est une option longue
			elif ol in self.options:
				if self.options[ol]['flag']:
					self.options[ ol ]['value'] = True
				else:
					self.options[ol]['value'] = a

		# retourne tout ce qui n'a pas été parsé
		return args


	def add( self, short, long, description, default='' ):
		flag = False
		if default==True or default==False:
			flag = True
		self.options [ long ] = { 'short':short, 'long':long, 'description':description, 'default':default, 'flag':flag, 'value':default }
	

	def print_usage(self):
		print self.usage
		
	        for o in self.options:
	                print "\t-%s, --%s\t%s." % ( self.options[o]['short'], self.options[o]['long'], self.options[o]['description'] )

	def print_state(self):
		print "Options settings:"
		for o in self.options:	
	                print "\t%s=%s (%s)" % ( self.options[o]['long'], self.options[o]['value'], self.options[o]['default'] )

	def get( self, long ):
		return self.options[long]['value']

#
# XPath-friendlier ElementTree namespace helper
# http://infix.se/2007/02/21/xpath-friendlier-elementtree-namespace-helper
#
class NS:
	def __init__(self, uri):
		self.uri = '{'+uri+'}'
	def __getattr__(self, tag):
		return self.uri + tag
	def __call__(self, path):
		return "/".join((tag not in ("", ".", "*"))
			and getattr(self, tag)
			or tag
			for tag in path.split("/"))


class Stripit:
	def __init__( self, verbose=False ):
		self.verbose = verbose

	#                                                                                                                                      
	# wrapper around PIL 1.1.6 Image.save to preserve PNG metadata
	#
	# public domain, Nick Galbreath                                                                                                        
	# http://blog.modp.com/2007/08/python-pil-and-png-metadata-take-2.html                                                                 
	#                                                                                                                                       
	def pngsave(self, im, file):
		# these can be automatically added to Image.info dict                                                                              
		# they are not user-added metadata
		reserved = ('interlace', 'gamma', 'dpi', 'transparency', 'aspect')

		# undocumented class
		from PIL import PngImagePlugin
		meta = PngImagePlugin.PngInfo()

		# copy metadata into new object
		for k,v in im.info.iteritems():
			if k in reserved: continue
			meta.add_text(k, v, 0)

		# and save
		im.save(file, "PNG", pnginfo=meta)


	def export( self, file_we, max_size=None, options='' ):
		"""file name only, without extension"""

		if self.verbose:
			print '\tCreating the PNG from the SVG...',
			sys.stdout.flush()

		size_arg=''
		if max_size:
			w = os.popen( 'inkscape --query-width %s.svg' % (file_we) )
			h = os.popen( 'inkscape --query-height %s.svg' % (file_we) )

			width = float(w.read())
			height = float(h.read())
			ratio = height/width
			max_size = float( max_size )

			w.close()
			h.close()

			if height > width:
				size_arg += ' --export-height=%f' % (max_size)
				size_arg += ' --export-width=%f' % (max_size / ratio)
			else:
				size_arg += ' --export-height=%f' % (max_size * ratio)
				size_arg += ' --export-width=%f' % (max_size)
			

		cmd = 'inkscape -z %s --export-png %s.png %s.svg ' % (size_arg, file_we,file_we)
		cmd += options
		
		#if self.verbose:
		#	print ' " %s " ' % (cmd)
		#	sys.stdout.flush()

		os.popen( cmd )

		if self.verbose:
			print '\tok'

	
	def xfind( self, tree, ns ):
		# on ne veut passer qu'un liste d'élements
		q = '//' + '/'.join( ns )
		
		# requête sur le xml
		res = unicode( tree.findtext( q ) )

		# la norme PNG demande de l'ASCII, on essaye de convertir au mieux
		return unicodedata.normalize('NFKD', res ).encode('ASCII', 'ignore')	
	

	def get_svg_metadata( self, file_we ):

		if self.verbose:
			print '\tGet SVG metadata...',
			sys.stdout.flush()
		
		# raccourcis pour les namespaces	
		DC  = NS('http://purl.org/dc/elements/1.1/')
		CC  = NS('http://web.resource.org/cc/')
		RDF = NS('http://www.w3.org/1999/02/22-rdf-syntax-ns#')
		SVG = NS('http://www.w3.org/2000/svg')

		tree = ElementTree.parse( file_we + '.svg')

		# PNG metadata textual informations :
		# (from http://www.libpng.org/pub/png/spec/1.2/PNG-Chunks.html#C.Anc-text )
		#
		#   Title            Short (one line) title or caption for image
		#   Author           Name of image's creator
		#   Description      Description of image (possibly long)
		#   Copyright        Copyright notice
		#   Creation Time    Time of original image creation
		#   Software         Software used to create the image
		#   Disclaimer       Legal disclaimer
		#   Warning          Warning of nature of content
		#   Source           Device used to create the image
		#   Comment          Miscellaneous comment; conversion from
		#                    GIF comment

		metadata = {}
		metadata['Author']	= self.xfind( tree, [ CC('Agent'),  DC('title') ] )
		metadata['Description']	= self.xfind( tree, [ CC('Work'), DC('description') ])
		metadata['Copyright']	= self.xfind( tree, [ CC('Work'), CC('license') ] ) # FIXME ne marche pas
		metadata['Creation Time'] = self.xfind( tree, [ CC('Work'), DC('date') ] )
		metadata['Software'] 	= 'www.inkscape.org / stripit.sourceforge.net' # FIXME pas très élégant
		metadata['Disclaimer']	=''
		metadata['Warning']	= ''
		metadata['Source']	= self.xfind( tree, [ CC('Work'), DC('source') ])
		lang = self.xfind( tree, [ CC('Work'), DC('language') ])
		metadata['Comment']	= 'Language: %s' % lang

		if self.verbose:
			print '\t\t\tok'
		return metadata


	def save_png_with_metadata( self, file_we, metadata ):
		if self.verbose:
			print '\tWrite metadata in the PNG...\t',
			sys.stdout.flush()

		Image.init()
		im = Image.open( file_we+'.png' )
		im.info = metadata
		#print im.info
		self.pngsave( im, file_we+'.png' )
		if self.verbose:
			print '\tok'


	def upload( self, file_we, host, user, dir, password ):
		if self.verbose:
			print '\tUpload of %s on %s.\t' % (file_we, host),
			sys.stdout.flush()
		ftp = ftplib.FTP( host, user, password )
		ftp.cwd( dir )

		if self.verbose:
			print '.',
			sys.stdout.flush()

		f_png = open( '%s.png' % file_we )
		ftp.storbinary( 'STOR %s.png' % file_we, f_png )
		f_png.close()

		if self.verbose:
			print '.',
			sys.stdout.flush()

		f_svg = open( '%s.svg' % file_we )
		ftp.storbinary( 'STOR %s.svg' % file_we, f_svg )
		f_svg.close()

		ftp.quit()
		
		if self.verbose:
			print '\ŧok'



if __name__=="__main__":

	usage = """Script d'aide à l'export et au téléchargement pour StripIt.
\tCe script va exporter un fichier SVG au format PNG, puis télécharger le tout sur un serveur FTP.
Usage: stripit.py [OPTIONS] fichier"""

	oo = Options( sys.argv, usage )

	oo.add( 'H',	'host',		'Serveur FTP',			'' )
	oo.add( 'u',	'user',		'Login FTP',			'anonymous' )
	oo.add( 'd',	'dir',		'Dossier FTP',			'strips' )
	oo.add( 'p',	'port',		'Port FTP',			'21' )
	oo.add( 'P',	'pass',		'Mot de passe FTP',		'' )
	oo.add( 'x',	'no-export',	"Pas d'export PNG",		False )
	oo.add( 'n',	'no-upload',	'Pas de téléchargement FTP',	False )	
	oo.add(	'v',	'verbose',	"Afficher plus d'informations",	False )
	oo.add( 's',	'supp',		'Options supplémentaires pour inkscape', '' )
	oo.add( 'h',	'help',		"Ce message d'aide",		False )
	oo.add(	'm',	'max-size',	'Taille maximale en hauteur ou en largeur, en pixels',	'800' )

	args = oo.parse()
	#oo.print_state()
	
	if oo.get('help'):
		oo.print_usage()
		sys.exit()
	
	while args:

		f = args.pop()
	
		# supprime l'extension	
		f = os.path.splitext( f ) [0]
	
		if oo.get('verbose'):
			print "Processing %s:" % os.path.basename( f )
		
		si = Stripit( oo.get('verbose') )
		
		if not oo.get('no-export'):
			si.export( f, oo.get('max-size'), oo.get('supp') )
	
		md = si.get_svg_metadata( f )
	
		si.save_png_with_metadata( f, md )
	
		if not oo.get('no-upload'):
			si.upload( 
				f, 
				oo.get('host'), 
				oo.get('user'), 
				oo.get('dir'), 
				oo.get('pass')
			 ) # FIXME options ou .conf ?

