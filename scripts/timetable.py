import sys
import json
import datetime
import requests
import bs4


class Parser:
    def __init__(self):
        self.session = requests.Session()
        self.update_csrf_token()

    def update_csrf_token(self) -> None:
        response = self.session.get('https://ssau.ru/rasp')
        bs = bs4.BeautifulSoup(response.text, 'html.parser')
        token = bs.select_one('meta[name="csrf-token"]')['content']
        self.session.headers.update({'X-Csrf-Token': token})


    def get_group_id(self, query: str) -> int:
        self.session.headers.update({'Accept': 'application/json'})
        response = self.session.post('https://ssau.ru/rasp/search', data={'text': query})
        return response.json()[0]['id']


    def get_timetable_page(self, group_id: int, week: int = 1, day: int = 1) -> bs4.BeautifulSoup:
        self.session.headers.update({'Accept': '*/*'})
        response = self.session.get(f'https://ssau.ru/rasp?groupId={group_id}&selectedWeek={week}&selectedWeekday={day}')
        return bs4.BeautifulSoup(response.text, 'html.parser')


    def get_timetable(self, group_id: int, date: datetime) -> list:
        bs_start = self.get_timetable_page(group_id)
        date_start = datetime.datetime.strptime(bs_start.select_one('.schedule__head-date').text.strip(), '%d.%m.%Y')
        week = (date - date_start).days // 7 + 1
        day = (date - date_start).days % 7 + 1
        bs = self.get_timetable_page(group_id, week, day)

        pairs = []
        pair_types = {
            'Лекция': 'lecture',
            'Практика': 'practice',
            'Лабораторная': 'lab',
            'Другое': 'other',
        }

        pair_numbers = {
            '08:00' : 1,
            '09:45' : 2,
            '11:30' : 3,
            '13:30' : 4,
            '15:15' : 5,
            '17:00' : 6,
            '18:45' : 7,
            '20:25' : 8,
        }

        for pair, time in zip(bs.select('.schedule__item_show'), bs.select('.schedule__time')):
            if pair.text:
                subpairs = [{}, {}, {}, {}, {}, {}, {}, {}]
                number = pair_numbers[time.select_one('.schedule__time-item').text.strip()]
                delimiter = ' / '

                for i, subpair in enumerate(map(lambda p: p.text.strip(), pair.select('.schedule__lesson-type-chip'))):
                    subpairs[i]['type'] = pair_types.get(subpair, 'other')

                for i, subpair in enumerate(map(lambda p: p.text.strip(), pair.select('.schedule__discipline'))):
                    subpairs[i]['name'] = subpair

                for i, subpair in enumerate(map(lambda p: p.text.strip(), pair.select('.schedule__place'))):
                    subpairs[i]['place'] = subpair

                for i, subpair in enumerate(map(lambda p: p.text.strip(), pair.select('.schedule__teacher'))):
                    subpairs[i]['teacher'] = subpair

                for i, subpair in enumerate(map(lambda p: p.text.strip(), pair.select('.schedule__groups'))):
                    groups = subpair.split() if '-' in subpair else [subpair]
                    subpairs[i]['groups'] = delimiter.join(list(map(lambda p: p.split('-')[0], groups)))

                pairs.extend(list(map(lambda s: dict(number=number, **s), filter(lambda p: p, subpairs))))
        return pairs


def main():
    try:
        parser = Parser()
        print(json.dumps({
            'success': True,
            'data': parser.get_timetable(
                parser.get_group_id(sys.argv[1]),
                datetime.datetime.strptime(sys.argv[2], '%d.%m.%Y')
            )
        }))
    except Exception as e:
        print(json.dumps({'success': False, 'data': [], 'message': str(e)}))


if __name__ == '__main__':
    main()
