date



SOURCE="${BASH_SOURCE[0]}"

while [ -h "$SOURCE" ]; do # resolve $SOURCE until the file is no longer a symlink

  DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"

  SOURCE="$(readlink "$SOURCE")"

  [[ $SOURCE != /* ]] && SOURCE="$DIR/$SOURCE" # if $SOURCE was a relative symlink, we need to resolve it relative to the path where the symlink file was located

done

DIR="$( cd -P "$( dirname "$SOURCE" )" && pwd )"


FILES="$DIR/flickrscripts/* $DIR/rssscripts/* $DIR/oaiscripts/* $DIR/instagramscripts/* $DIR/sketchfab_collectionscripts/* $DIR/tesscripts/* $DIR/twitterscripts/* $DIR/wiki_user_file_contribscripts/* $DIR/slidesharescripts/* $DIR/youtubescripts/*"


for f in $FILES

do

	if [[ "$f" == *"harvestscript"* ]]; then

		php -q $f

	fi

done



date